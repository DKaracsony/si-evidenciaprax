<?php

namespace App\Observers;

use App\Models\InternshipStatusHistory;
use App\Services\Cache\InternshipStatusService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class InternshipStatusHistoryObserver
{
    public function created(InternshipStatusHistory $statusHistory): void
    {
        $statusHistory->load('internship.studentProfile', 'internship.company');

        $statuses   = new InternshipStatusService();
        $statusName = optional($statuses->all()->firstWhere('id', $statusHistory->status_id))->name;

        if (!$statusName) {
            Log::warning("No status name found for history id {$statusHistory->id}");
            return;
        }

        $rules = config("internship_notification_rules.statuses.$statusName");

        if (!$rules || empty($rules)) {
            Log::info("No notification rules defined for status: $statusName");
            return;
        }

        $notificationService = new NotificationService($statusHistory->internship, $rules, $statusName);
    }

}
