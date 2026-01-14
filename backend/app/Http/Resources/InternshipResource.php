<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InternshipResource extends JsonResource
{
    // app/Http/Resources/InternshipResource.php

    public function toArray($request): array
    {

        $u = $this->studentProfile?->user;

        $studentFullName = trim(
            ($u?->title_before ? $u->title_before . ' ' : '') .
            ($u?->first_name ?? '') . ' ' .
            ($u?->last_name ?? '') .
            ($u?->title_after ? ', ' . $u->title_after : '')
        );

        return [
            'id' => $this->id,

            'student' => [
                'id' => $this->studentProfile?->id,
                'first_name' => $u?->first_name,
                'last_name' => $u?->last_name,
                'title_before' => $u?->title_before,
                'title_after' => $u?->title_after,
                'full_name' => $studentFullName,
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
