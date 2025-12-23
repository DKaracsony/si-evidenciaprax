<?php


use App\Models\Internship;
use App\Models\Role;

if (!function_exists('getAgreementPath')) {
    function getAgreementPath($internship_id, $file_name)
    {
        return storage_path("app/private/internship_agreement/{$internship_id}/{$file_name}");
    }
}

if (!function_exists('hasInternshipRelationToLoggedUser')) {
    function hasInternshipRelationToLoggedUser($user, $internshipId)
    {
        $hasAccess = false;
        switch ($user->role->name) {
            case Role::STUDENT:
                $hasAccess = Internship::where('id', $internshipId)
                    ->whereHas('studentProfile.user', fn($q) => $q->whereKey($user->id))
                    ->exists();
                break;
            case Role::COMPANY:
                $hasAccess = Internship::where('id', $internshipId)
                    ->whereHas('company.ownerProfiles.user', fn($q) => $q->whereKey($user->id))
                    ->exists();
                break;
            case Role::GARANT:
                $hasAccess = true;
                break;
        }
        return $hasAccess;
    }
}
