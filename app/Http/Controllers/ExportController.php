<?php

namespace App\Http\Controllers;

use App\Services\InternshipQueryBuilder;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportInternshipsCsv(Request $request)
    {
        $query = InternshipQueryBuilder::fromRequest($request);
        $internships = $query->get();

        if ($internships->isEmpty()) {
            return response()->json(['message' => __('internship.NO_DATA_TO_EXPORT')], 404);
        }

        return null; //TODO: Atus - dokoncit export do csv, filtre su hotove, netreba to ulozit do suboru, staci vratit ako stiahnuty subor
    }
}
