<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Internship;
use App\Models\Status;
use App\Services\Cache\InternshipStatusService;

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
}
