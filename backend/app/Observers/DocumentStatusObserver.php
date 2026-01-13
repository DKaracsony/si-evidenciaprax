<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\DocumentStatus;
use App\Models\Notification;
use App\Models\StudentProfile;

class DocumentStatusObserver
{
    public function updated(DocumentStatus $status): void
    {
        if ($status->wasChanged('decision')) {
             $documentID = Document::where('document_status_id', $status->id)->first()->id;
             $studentID = Document::where('id', $documentID)->first()->internship->student_profile_id;
             $userID = StudentProfile::where('id', $studentID)->first()->user_id;

             if ($userID) {
                 Notification::create([
                     'text' => __('notification.DOCUMENT_STATUS_CHANGED'),
                     'type' => Notification::INFORMATION,
                     'emailed_at' => null,
                     'sent_at' => now(),
                     'receiver_user_id' => $userID,
                 ]);
             }
        }
    }
}
