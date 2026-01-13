<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use App\Services\Cache\RoleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private $internship;
    private $rules;
    private $statusName;
    private $internshipNote;
    private $emails = [];

    public function __construct($internship,$rules,$statusName,$internshipNote = null)
    {
        $this->internship = $internship;
        $this->rules = $rules;
        $this->statusName = $statusName;
        $this->internshipNote = $internshipNote;

        $this->sendStatusChangeNotification();
    }

    public function sendStatusChangeNotification()
    {
        $notificationsToCreate = [];

        if(in_array(Role::STUDENT, $this->rules['recipient'])) {
            $studentNotifications = $this->notificationToStudent();
            if ($studentNotifications)
                $notificationsToCreate = array_merge($notificationsToCreate, $studentNotifications);
        }


        if(in_array(Role::GARANT, $this->rules['recipient'])) {
            $garantNotifications = $this->notificationToGarants();
            if($garantNotifications) {
                $notificationsToCreate = array_merge($notificationsToCreate, $garantNotifications);
            }
        }

        if(in_array(Role::COMPANY, $this->rules['recipient']))
            if ($companyNotification = $this->notificationToCompany())
                $notificationsToCreate[] = $companyNotification;

        Notification::insert($notificationsToCreate);
        $this->sendNotificationEmail();
    }


    private function notificationToStudent(){
        $userID = $this->internship->studentProfile?->user?->id;

        if(!$userID) {
            Log::warning("No student user found for internship id {$this->internship->id}");
            return null;
        }

        $notifications = [];
        $notifications[] = [
            'text' => $this->rules['notification_text_key']
                ? __($this->rules['notification_text_key'], [
                    'company'  => optional($this->internship->company)->name,
                    'reason'   => $this->internshipNote ?? '-',
                ])
                : '',
            'type' => Notification::STATUS_CHANGED,
            'emailed_at' => $this->rules['email'] ? now() : null,
            'sent_at' => now(),
            'receiver_user_id' => $userID,
        ];

        if($this->rules['email']){
            $this->emails[] = [
                'to' => $this->internship->studentProfile->user->email,
                'user_type' => Role::STUDENT,
                'email_key' => $this->rules['email_key']
            ];
        }

        if($this->statusName == Status::ACCEPTED){ // vyzva na nahranie zmluvy
            $notifications[] = [
                'text' => __('notification.PLEASE_UPLOAD_AGREEMENT', [
                    'company'  => optional($this->internship->company)->name,
                ]),
                'type' => Notification::INFORMATION,
                'emailed_at' => null,
                'sent_at' => now(),
                'receiver_user_id' => $userID,
            ];
        }

        return $notifications;
    }

    private function notificationToGarants(){
        $notifications = [];
        $roleService = new RoleService();
        $garantRoleID = $roleService->all()->firstWhere('name', Role::GARANT)->id;
        $facultyId = $this->internship->studentProfile->faculty_id;

        $garantUsers = User::query()
            ->where('role_id', $garantRoleID)
            ->where(function ($q) use ($facultyId) {
                $q->whereHas('garantProfile.faculties', function ($f) use ($facultyId) {
                    $f->where('faculties.id', $facultyId);
                })
                    ->orWhereHas('garantProfile', function ($gp) {
                        $gp->whereDoesntHave('faculties');
                    })
                    ->orWhereDoesntHave('garantProfile');
            })
            ->get(['id', 'email'])
            ->toArray();

        if(empty($garantUsers)) {
            Log::warning("No garant users found for internship id {$this->internship->id}");
            return null;
        }

        foreach($garantUsers as $garantUser) {
            $notifications[] = [
                'text' => $this->rules['notification_text_garant_key']
                    ? __($this->rules['notification_text_garant_key'], [
                        'company'  => optional($this->internship->company)->name,
                        'student'  => optional($this->internship->studentProfile->user)->first_name . ' ' . optional($this->internship->studentProfile->user)->last_name,
                    ])
                    : '',
                'type' => Notification::STATUS_CHANGED,
                'emailed_at' => $this->rules['email'] ? now() : null,
                'sent_at' => now(),
                'receiver_user_id' => $garantUser['id'],
            ];

            if($this->rules['email']){
                $this->emails[] = [
                    'to' => $garantUser['email'],
                    'user_type' => Role::GARANT,
                    'email_key' => $this->rules['email_key']
                ];
            }
        }

        return $notifications;
    }

    private function notificationToCompany(){
        $companyUserID = $this->internship->company->ownerProfiles->user->id;
        if(!$companyUserID) {
            Log::warning("No company owner user found for internship id {$this->internship->id}");
            return null;
        }

        $notification = [
            'text' => $this->rules['notification_text_company_key']
                ? __($this->rules['notification_text_company_key'], [
                    'student'  => optional($this->internship->studentProfile->user)->first_name . ' ' . optional($this->internship->studentProfile->user)->last_name,
                ])
                : '',
            'type' => Notification::STATUS_CHANGED,
            'emailed_at' => $this->rules['email'] ? now() : null,
            'sent_at' => now(),
            'receiver_user_id' => $companyUserID,
        ];

        if($this->rules['email']){
            $this->emails[] = [
                'to' => $this->internship->company->ownerProfiles->user->email,
                'user_type' => Role::COMPANY,
                'email_key' => $this->rules['email_key']
            ];
        }

        return $notification;
    }

    private function sendNotificationEmail(){
        foreach ($this->emails as $email){
            $mailService = new MailSender(
                $email['email_key'],
                [$email['to']],
                [
                    'to_role'               => $email['user_type'],
                    'student_name'          => $this->internship->studentProfile->user->first_name . ' ' . $this->internship->studentProfile->user->last_name,
                    'company_profile_name'  => $this->internship->company->ownerProfiles->user->first_name . ' ' . $this->internship->company->ownerProfiles->user->last_name,
                    'company_name'          => $this->internship->company->name ?? '',
                    'academic_year'         => $this->internship->academicYear->season ?? '',
                    'start_date' => $this->internship->start_date
                        ? Carbon::parse($this->internship->start_date)->format('d.m.Y')
                        : '',

                    'end_date' => $this->internship->date_to
                        ? Carbon::parse($this->internship->date_to)->format('d.m.Y')
                        : '',
                    'note'                  => $this->internshipNote ?? '',
                ]
                );

            $mailService->send();
        }
    }
}
