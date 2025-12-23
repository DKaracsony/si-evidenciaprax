<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Services\Cache\RoleService;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private $internship;
    private $rules;
    private $statusName;
    private $emails = [];

    public function __construct($internship,$rules,$statusName)
    {
        $this->internship = $internship;
        $this->rules = $rules;
        $this->statusName = $statusName;

        $this->sendStatusChangeNotification();
    }

    public function sendStatusChangeNotification()
    {
        $notificationsToCreate = [];

        if(in_array(Role::STUDENT, $this->rules['recipient']))
            if ($studentNotification = $this->notificationToStudent())
                $notificationsToCreate[] = $studentNotification;


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

        $notification = [
            'text' => $this->rules['notification_text_key']
                ? __($this->rules['notification_text_key'], [
                    'company'  => optional($this->internship->company)->name,
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
            ];
        }

        return $notification;
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
            ];
        }

        return $notification;
    }

    private function sendNotificationEmail(){
        //TODO: Atus doplnit emailovu notifikaciu, v subore internship_notification_rules.php vidis pre každy status ci sa ma posielať email alebo nie
        //TODO: $emails strukturu pozri vyssie
        //TODO: teda tvoja uloha iba vytvorit mail sablon(i) a poslat emaily pomocou MailSender triedy + otestovat cele
        //TODO: + logiku vies vytvorit podla $statusName, \App\Models\Status mas tam const premenne na hodnoty statusov a $statusName bude sediet s tymi hodnotami
        //TODO: a v $emails budes mat emaily komu sa maju poslat, emailovu adresu + typ uzivatela. V \App\Models\Role mas zase const premenne s typmi uzivatelov
        /*foreach ($this->emails as $email){
            $mailService = new MailSender(

                );
        }*/
    }
}
