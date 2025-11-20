<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $service) {}

    public function forgot(Request $r)
    {
        $data = $r->validate(['email' => 'required|email:rfc,strict']);
        $this->service->issueTokenAndSendMail($data['email']);
        return response()->json([
            'message' => 'Ak existuje účet, poslali sme e-mail s ďalším postupom.'
        ]);
    }

    public function reset(Request $r)
    {
        $data = $r->validate([
            'email'    => 'required|email:rfc,strict',
            'token'    => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->service->resetPassword(
            $data['email'],
            $data['token'],
            $data['password']
        );

        return response()->json([
            'message' => 'Ak je token platný, heslo bolo zmenené.'
        ]);
    }

    public function changePassword(Request $r)
    {
        $user = $r->user();

        // základné pravidlá – nové heslo
        $rules = [
            'new_password' => 'required|string|min:8|confirmed',
            // v requeste musí byť new_password_confirmation
        ];

        // ak nejde o povinnú prvú zmenu hesla, vyžadujeme current_password
        if (!$user->password_reset_needed) {
            $rules['current_password'] = 'required|string';
        }

        $validator = Validator::make($r->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Neplatné vstupy.',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        // Kontrola aktuálneho hesla len v "bežnom" prípade
        if (!$user->password_reset_needed) {
            if (!Hash::check($r->input('current_password'), $user->password_hash)) {
                return response()->json([
                    'message' => 'Aktuálne heslo je nesprávne.',
                ], 422);
            }
        }

        // Samotná zmena hesla (v oboch prípadoch rovnaká)
        $user->password_hash = Hash::make($r->input('new_password'));
        $user->password_reset_needed = false;
        $user->save();

        return response()->json([
            'message' => 'Heslo bolo úspešne zmenené.',
        ]);
    }

}
