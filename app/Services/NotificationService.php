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

            $garantIDs = User::where('role_id',$garantRoleID)->pluck('id')->toArray();

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
