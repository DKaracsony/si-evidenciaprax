<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // (Opcionális) csak hallgató láthatja – akkor kap normális 403-at
        // ha nálatok van pl. role mező:
        /*
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        */

        // Kiinduló query: csak a BEJELENTKEZETT hallgató praxijai
        // Igazítsd: student_id vs student_profile_id, attól függően milyen a táblátok
        $query = Internship::query()
            ->where('student_profile_id', $user->studentProfile->id);

        // 🔍 Filtrovanie podľa statusu: ?status=approved
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 🔍 Filtrovanie podľa akademického roka: ?academic_year_id=3
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', (int) $request->get('academic_year_id'));
        }

        // 🔍 Dátumové filtrovanie (ak to biznis chce):
        // ?from=2025-02-01&to=2025-06-30
        if ($request->filled('from')) {
            $query->whereDate('date_from', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date_to', '<=', $request->get('to'));
        }

        // Esetleg rendezés – legújabb elöl
        $internships = $query
            ->orderByDesc('created_at')
            ->get();

        return response()->json($internships);
    }
}
