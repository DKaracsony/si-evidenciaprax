<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class InternshipStatsController extends Controller
{
    public function statusCounts()
    {
        $rows = DB::table('internship_status_histories as ish')
            ->join(
                DB::raw('(
                    SELECT internship_id, MAX(status_changed_at) as last_change
                    FROM internship_status_histories
                    GROUP BY internship_id
                ) as latest'),
                function ($join) {
                    $join->on('ish.internship_id', '=', 'latest.internship_id')
                        ->on('ish.status_changed_at', '=', 'latest.last_change');
                }
            )
            ->join('statuses as s', 's.id', '=', 'ish.status_id')
            ->select('s.name', DB::raw('COUNT(*) as count'))
            ->groupBy('s.name')
            ->orderBy('s.name')
            ->get();

        return response()->json($rows->pluck('count', 'name'));
    }
}
