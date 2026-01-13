<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        // "Neaktuálny" = start_date je starší ako 1 rok od dneška.
        $minStartDate = now()->subYear()->toDateString();

        $items = AcademicYear::query()
            ->where('start_date', '>=', $minStartDate)
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'data' => $items->map(fn (AcademicYear $y) => [
                'id'         => $y->id,
                'start_date' => (string) $y->start_date,
                'end_date'   => (string) $y->end_date,
                'season'     => $y->season,
            ]),
        ]);
    }
}
