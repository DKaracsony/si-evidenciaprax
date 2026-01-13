<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'         => ['sometimes', 'integer', 'exists:companies,id'],
            'student_profile_id' => ['sometimes', 'integer', 'exists:student_profiles,id'],

            'start_date' => ['sometimes', 'date', 'required_with:date_to'],
            'date_to'    => ['sometimes', 'date', 'required_with:start_date'],

            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],

            'description' => ['sometimes', 'nullable', 'string'],
            'is_draft'    => ['sometimes', 'boolean'],

            'status_id' => ['sometimes', 'integer', 'exists:statuses,id'],
            'note'      => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $internship = $this->route('internship');

            $start = $this->input('start_date') ?? ($internship?->start_date);
            $end   = $this->input('date_to')    ?? ($internship?->date_to);

            if (!$start && !$end) {
                return;
            }

            if (!$start || !$end) {
                $validator->errors()->add('start_date', 'Dátumy praxe musia byť vyplnené (od–do).');
                $validator->errors()->add('date_to', 'Dátumy praxe musia byť vyplnené (od–do).');
                return;
            }

            if (strtotime($start) > strtotime($end)) {
                $validator->errors()->add('start_date', 'Dátum začiatku nesmie byť neskorší ako dátum ukončenia.');
                return;
            }

            $academicYearId = $this->input('academic_year_id') ?? ($internship?->academic_year_id);

            if (!$academicYearId) {
                $validator->errors()->add('academic_year_id', 'Pri zadaní dátumov je povinné zvoliť semester.');
                return;
            }

            $academicYear = AcademicYear::find($academicYearId);
            if (!$academicYear) {
                $validator->errors()->add('academic_year_id', 'Zvolený semester neexistuje.');
                return;
            }

            $semesterStart = $academicYear->start_date->format('Y-m-d');
            $semesterEnd   = $academicYear->end_date->format('Y-m-d');

            if ($start < $semesterStart || $start > $semesterEnd) {
                $validator->errors()->add('start_date', "Dátum začiatku musí byť v rozsahu zvoleného semestra ({$semesterStart} – {$semesterEnd}).");
            }

            if ($end < $semesterStart || $end > $semesterEnd) {
                $validator->errors()->add('date_to', "Dátum ukončenia musí byť v rozsahu zvoleného semestra ({$semesterStart} – {$semesterEnd}).");
            }
        });
    }

}
