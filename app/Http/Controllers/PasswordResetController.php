<?php

namespace App\Http\Controllers;

use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function __construct(private PasswordResetService $service) {}

    public function forgot(Request $r)
    {
        $data = $r->validate(['email' => 'required|email:rfc,strict']);
        $this->service->issueTokenAndSendMail($data['email'], $r->ip(), $r->userAgent());
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
}
