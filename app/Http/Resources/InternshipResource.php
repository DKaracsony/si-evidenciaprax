<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student' => [
                'id' => $this->studentProfile?->user?->id,
                'first_name' => $this->studentProfile?->user?->first_name,
                'last_name' => $this->studentProfile?->user?->last_name,
                'title_before' => $this->studentProfile?->user?->title_before,
            ],
            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
                'owner' => [
                    'id' => $this->company?->ownerProfiles?->user?->id,
                    'first_name' => $this->company?->ownerProfiles?->user?->first_name,
                    'last_name' => $this->company?->ownerProfiles?->user?->last_name,
                    'title_before' => $this->company?->ownerProfiles?->user?->title_before,
                ],
            ],
            'status' => [
                'name'=> $this->internshipStatusHistories->last()?->status->name,
                'changed_at' => $this->internshipStatusHistories->last()?->status_changed_at,
            ],
            'start_date' => $this->start_date,
            'end_date' => $this->date_to,
            'semester' => [
                'id' => $this->academicYear?->id,
                'season' => $this->academicYear?->season,
                'start_date' => $this->academicYear?->start_date,
                'end_date' => $this->academicYear?->end_date,
            ],
        ];
    }
}
