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

        // FIRMA
        if ($request->filled('company_ids')) {
            $query->whereIn('company_id', $request->input('company_ids'));
        }

        // STUDENT
        if ($request->filled('student_ids')) {
            $studentUserIds = $request->input('student_ids');
            $query->whereHas('studentProfile', function ($q) use ($studentUserIds) {
                $q->whereIn('student_user_id', $studentUserIds);
            });
        }

        // ODBOR
        if ($request->filled('faculty_ids')) {
            $facultyIds = $request->input('faculty_ids');
            $query->whereHas('studentProfile', function ($q) use ($facultyIds) {
                $q->whereIn('faculty_id', $facultyIds);
            });
        }

        return $query;
    }
}
