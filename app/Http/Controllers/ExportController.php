<?php

namespace App\Http\Controllers;

use App\Services\InternshipQueryBuilder;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportInternshipsCsv(Request $request)
    {
        $query = InternshipQueryBuilder::fromRequest($request);

        $internships = $query
            ->with([
                'studentProfile.user',
                'company',
                'academicYear',
                'internshipStatusHistories' => fn ($q) => $q->orderByDesc('status_changed_at'),
                'internshipStatusHistories.status',
            ])
            ->get();

        if ($internships->isEmpty()) {
            return response()->json(['message' => __('internship.NO_DATA_TO_EXPORT')], 404);
        }

        $fileName = 'internships_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($internships) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Student name',
                'Student email',
                'Company',
                'Academic year',
                'Status',
                'Practice type',
                'Start date',
                'End date',
                'Submitted at',
            ]);

            foreach ($internships as $i) {
                $user = $i->studentProfile?->user;

                $studentName = trim(implode(' ', array_filter([
                    $user?->title_before,
                    $user?->first_name,
                    $user?->last_name,
                    $user?->title_after,
                ])));

                $ay = $i->academicYear;
                $academicYear = null;

                if ($ay && $ay->start_date && $ay->end_date) {
                    $startYear = \Carbon\Carbon::parse($ay->start_date)->year;
                    $endYear   = \Carbon\Carbon::parse($ay->end_date)->year;

                    $academicYear = $startYear . '/' . $endYear;

                    if (!empty($ay->season)) {
                        $academicYear .= ' (' . $ay->season . ')';
                    }
                }

                $statusName = $i->internshipStatusHistories->first()?->status?->name
                    ?? \App\Models\Status::CREATED;

                fputcsv($out, [
                    $studentName ?: null,
                    $user?->email,
                    $i->company?->name,
                    $academicYear,
                    $statusName,
                    $this->humanPracticeType($i->practice_type),
                    $this->fmtDate($i->start_date),
                    $this->fmtDate($i->date_to),
                    $this->fmtDateTime($i->submitted_at),
                ]);
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function humanPracticeType(?string $type): ?string
    {
        return match ($type) {
            \App\Models\Internship::PRACTICE_TYPE_STANDARD => 'Standard',
            \App\Models\Internship::PRACTICE_TYPE_PAID_EMPLOYMENT_CONTRACT => 'Paid (employment contract)',
            \App\Models\Internship::PRACTICE_TYPE_PAID_INVOICES => 'Paid (invoices)',
            default => $type,
        };
    }

    private function fmtDate($value): ?string
    {
        if (empty($value)) return null;
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    private function fmtDateTime($value): ?string
    {
        if (empty($value)) return null;
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
