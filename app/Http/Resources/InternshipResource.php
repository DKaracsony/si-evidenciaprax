<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipResource extends JsonResource
{
    // app/Http/Resources/InternshipResource.php

    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'student' => [
                // IMPORTANT: this MUST be student_profiles.id
                'id' => $this->studentProfile?->id,

                // user data stays the same
                'first_name' => $this->studentProfile?->user?->first_name,
                'last_name' => $this->studentProfile?->user?->last_name,
                'title_before' => $this->studentProfile?->user?->title_before,
            ],


            'faculty' => $this->studentProfile?->faculty ? [
                'id' => $this->studentProfile->faculty->id,
                'name' => $this->studentProfile->faculty->name,
            ] : null,

            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
            ],

            'status' => [
                'name'=> $this->internshipStatusHistories->last()?->status->name,
                'changed_at' => $this->internshipStatusHistories->last()?->status_changed_at,
            ],

            'start_date' => $this->start_date,
            'end_date' => $this->date_to,

            'description' => $this->description,

            'semester' => [
                'id' => $this->academicYear?->id,
                'season' => $this->academicYear?->season,
                'start_date' => $this->academicYear?->start_date,
                'end_date' => $this->academicYear?->end_date,
            ],
        ];
    }

}
