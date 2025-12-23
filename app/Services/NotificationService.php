<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Services\Cache\RoleService;
use Illuminate\Support\Facades\Log;

class NotificationService //TODO: doplnit neskor emailovu notfikaciu
{
    public function sendStatusChangeNotification($internship, $statusName, $rules)
    {
        $notificationsToCreate = [];

        if(in_array(Role::STUDENT, $rules['recipient'])) {
            $userID = $internship->studentProfile->user->id;

            if(!$userID) {
                Log::warning("No student user found for internship id {$internship->id}");
                return;
            }

            $notificationsToCreate[] = [
                'text' => $rules['notification_text_key']
                    ? __($rules['notification_text_key'], [
                        'company'  => optional($internship->company)->name,
                    ])
                    : '',
                'type' => Notification::STATUS_CHANGED,
                'emailed_at' => $rules['email'] ? now() : null,
                'sent_at' => now(),
                'receiver_user_id' => $userID,
            ];
        }

        if(in_array(Role::GARANT, $rules['recipient'])) {
            $roleService = new RoleService();
            $garantRoleID = $roleService->all()->firstWhere('name', Role::GARANT)->id;
            $facultyId = $internship->studentProfile->faculty_id;

            $garantIDs = User::query() //TODO: otestovat je to novinka od FR-07
                ->where('role_id', $garantRoleID)
                ->whereHas('garantProfile', function ($q) use ($facultyId) {
                    $q->where(function ($q2) use ($facultyId) {
                        // má priradenú fakultu študenta
                        $q2->whereHas('faculties', function ($q3) use ($facultyId) {
                            $q3->where('faculties.id', $facultyId);
                        })
                            // alebo garant nemá žiadne fakulty
                            ->orWhereDoesntHave('faculties');
                    });
                })
                ->pluck('id')
                ->toArray();

            if(empty($garantIDs)) {
                Log::warning("No garant users found for internship id {$internship->id}");
                return;
            }

            foreach($garantIDs as $garantID) {
                $notificationsToCreate[] = [
                    'text' => $rules['notification_text_garant_key']
                        ? __($rules['notification_text_garant_key'], [
                            'company'  => optional($internship->company)->name,
                            'student'  => optional($internship->studentProfile->user)->first_name . ' ' . optional($internship->studentProfile->user)->last_name,
                        ])
                        : '',
                    'type' => Notification::STATUS_CHANGED,
                    'emailed_at' => $rules['email'] ? now() : null,
                    'sent_at' => now(),
                    'receiver_user_id' => $garantID,
                ];
            }
        }

        Notification::insert($notificationsToCreate);
    }
}
