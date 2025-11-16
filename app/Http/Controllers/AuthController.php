<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function userDetails(Request $request)
    {
        $user = $request->user();

        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'title_before' => $user->title_before,
            'title_after' => $user->title_after,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'password_reset_needed' => $user->password_reset_needed,
            'role' => $user->role->name ?? null,
            'role_id' => $user->role->id ?? null,
            'address' => $user->user_address,
        ];

        if ($user->studentProfile)
            $userData['student_profile'] = [
                'student_profile_id' => $user->studentProfile->id,
                'personal_email' => $user->studentProfile->personal_email,
                'faculty_id' => $user->studentProfile->faculty?->id,
                'faculty_name' => $user->studentProfile->faculty?->name,
            ];

        if ($user->companyOwnerProfile)
            $userData['company_profile'] = [
                'active' => $user->companyOwnerProfile->is_active,
                'company_profile_id' => $user->companyOwnerProfile->id,
                'company_profile_role' => $user->companyOwnerProfile->role_at_company,
                'company_owner_profile' => $user->companyOwnerProfile->company->makeHidden(['address', 'address_id']),
            ];

        return response()->json(['user' => $userData]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => __('info_messages.LOGOUT_SUCCESS')]);
    }
}
