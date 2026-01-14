<?php

namespace App\Services;

use App\Models\Internship;
use Illuminate\Http\Request;

class InternshipQueryBuilder{
    public static function fromRequest(Request $request){
        $query = Internship::query()
            ->active()
            ->with(['studentProfile.user', 'company.ownerProfiles', 'academicYear', 'internshipStatusHistories.status'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        // FILTRACIA
        // ROK - START + END DATE
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('date_to', '<=', $request->input('date_to'));
        }

        // SEMESTER
        if ($request->filled('academic_year_ids')) {
            $query->whereIn('academic_year_id', $request->input('academic_year_ids'));
        }

        // SEMESTER SEASON (winter / summer)
        if ($request->filled('season')) {
            $seasons = (array) $request->input('season');

            $query->whereHas('academicYear', function ($q) use ($seasons) {
                $q->whereIn('season', $seasons);
            });
        }

        // FIRMA
        if ($request->filled('company_ids')) {
            $query->whereIn('company_id', $request->input('company_ids'));
        }

        // STUDENT
        if ($request->filled('student_ids')) {
            $studentProfileIds = (array) $request->input('student_ids');

            $query->whereIn('student_profile_id', $studentProfileIds);
        }

        // ODBOR
        if ($request->filled('faculty_ids')) {
            $facultyIds = $request->input('faculty_ids');
            $query->whereHas('studentProfile', function ($q) use ($facultyIds) {
                $q->whereIn('faculty_id', $facultyIds);
            });
        }

        // STAV (current status)
        if ($request->filled('status_names')) {
            $statusNames = $request->input('status_names');

            $query->whereHas('internshipStatusHistories', function ($q) use ($statusNames) {
                $q->whereIn('status_id', function ($sub) use ($statusNames) {
                    $sub->select('statuses.id')
                        ->from('statuses')
                        ->whereIn('statuses.name', $statusNames);
                })
                    ->whereRaw('internship_status_histories.status_changed_at = (
             SELECT MAX(ish2.status_changed_at)
             FROM internship_status_histories ish2
             WHERE ish2.internship_id = internship_status_histories.internship_id
        )');
            });
        }


        return $query;
    }
}
