<?php

namespace App\Http\Controllers;

use App\Services\InternshipQueryBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportInternshipsCsv(Request $request)
    {
        $query = InternshipQueryBuilder::fromRequest($request);
        $internships = $query->get();

        if ($internships->isEmpty()) {
            return response()->json(['message' => __('internship.NO_DATA_TO_EXPORT')], 404);
        }

        $fileName = 'internships_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($internships) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'id',
                'student_profile_id',
                'company_id',
                'academic_year_id',
                'start_date',
                'date_to',
                'is_draft',
                'submitted_at',
                'created_at',
                'updated_at',
            ]);

            foreach ($internships as $i) {
                fputcsv($out, [
                    $i->id,
                    $i->student_profile_id,
                    $i->company_id,
                    $i->academic_year_id,
                    $this->fmtDate($i->start_date),
                    $this->fmtDate($i->date_to),
                    (int) $i->is_draft,
                    $this->fmtDateTime($i->submitted_at),
                    $this->fmtDateTime($i->created_at),
                    $this->fmtDateTime($i->updated_at),
                ]);
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
