<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Internship;
use App\Models\Status;
use App\Services\Cache\InternshipStatusService;
use App\Services\MailSender;
use Illuminate\Support\Facades\Log;

class StatusChangeService
{
   private $intenshipId;
   private $newStatusId;
   private $statusService;
   private $finalStatusId;

   public function __construct() {
        $this->statusService = new InternshipStatusService();
        $this->finalStatusId = $this->statusService->all()->where('name', Status::DEFENDED)->first()->id;
   }

    public function setInternshipId($intenshipId)
    {
         $this->intenshipId = $intenshipId;
    }

    public function setNewStatusId($statusId)
    {
         $this->newStatusId = $statusId;
    }

    public function transitionAllowed() {
        $internship = Internship::where('id', $this->intenshipId)->with('internshipStatusHistories')->first();
        $lastStatusId = $internship->internshipStatusHistories->sortByDesc('status_changed_at')->first()->status->id;

        if(!hasInternshipRelationToLoggedUser(auth()->user(), $this->intenshipId))
            return __('global_error.UNAUTHORIZED');

        if($lastStatusId && $lastStatusId === $this->newStatusId)
            return __('internship.ALREADY_IN_DESIRED_STATUS');

        if($lastStatusId){
            $lastStatus = $this->statusService->all()->where('id', $lastStatusId)->first();
            $newStatus = $this->statusService->all()->where('id', $this->newStatusId)->first();

            if($newStatus->order_index < $lastStatus->order_index)
                return __('internship.STATUS_CHANGE_NOT_ALLOWED');

            if($newStatus->order_index != $lastStatus->order_index + 1 && $newStatus->order_index != $lastStatus->order_index)
                return __('internship.STATUS_CHANGE_PREVIOUS_STATUS_MISMATCH');
        }

        // ak ideme uzatvarat prax, cize menit jej status na obhajena, musime skontrolovat ci existuje zmluva (dokument)
        if($this->newStatusId == $this->finalStatusId) {
            $agreementDocumentExists = Document::where('internship_id', $this->intenshipId)
                ->where('type', Document::TYPE_AGREEMENT)
                ->exists();

            if(!$agreementDocumentExists)
                return __('internship.STATUS_CHANGE_NO_AGREEMENT');
        }


        return '';
    }

    public function sendEmailsForTransition(
        Internship $internship,
        Status $oldStatus,
        Status $newStatus,
        ?string $note = null
    ): void {
        $templateKey = $this->resolveTemplateKey($oldStatus, $newStatus);

        if ($templateKey === null) {
            return;
        }

        $recipients = $this->resolveRecipients($internship);

        if (empty($recipients)) {
            Log::warning('Status transition email skipped - no recipients', [
                'internship_id' => $internship->id,
                'from' => $oldStatus->name ?? null,
                'to' => $newStatus->name ?? null,
            ]);
            return;
        }

        $variables = [
            'student_name'  => $internship->studentProfile->user->first_name . ' ' . $internship->studentProfile->user->last_name,
            'company_name'  => $internship->company->name,
            'academic_year' => $internship->academicYear->season ?? '',
            'start_date'    => optional($internship->start_date)->format('d.m.Y'),
            'end_date'      => optional($internship->date_to)->format('d.m.Y'),
            'note'          => $note,
        ];

        (new MailSender($templateKey, $recipients, $variables))->send();
    }

    private function resolveTemplateKey(Status $oldStatus, Status $newStatus): ?string
    {
        $from = $oldStatus->name;
        $to   = $newStatus->name;

        // Potvrdená → Schválená
        if ($from === 'Potvrdená' && $to === 'Schválená') {
            return 'internship_confirmed_to_approved';
        }

        // Schválená → Obhájená
        if ($from === 'Schválená' && $to === 'Obhájená') {
            return 'internship_approved_to_defended';
        }

        // Schválená → Neobhájená
        if ($from === 'Schválená' && $to === 'Neobhájená') {
            return 'internship_approved_to_not_defended';
        }

        return null;
    }

    private function resolveRecipients(Internship $internship): array
    {
        $studentEmail =
            $internship->studentProfile?->user?->email
            ?? $internship->student?->user?->email
            ?? $internship->student?->email
            ?? null;

        $companyEmail =
            $internship->company?->ownerProfiles?->first()?->user?->email
            ?? $internship->company?->email
            ?? $internship->company?->contact_email
            ?? null;

        return array_values(array_unique(array_filter([$studentEmail, $companyEmail])));
    }
}
