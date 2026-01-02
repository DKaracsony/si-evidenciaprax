<?php

namespace App\Http\Controllers;

use App\Services\Cache\FacultyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GarantController extends Controller
{
    public function __construct(private FacultyService $facultyService)
    {}

    public function getMyFaculties(Request $request)
    {
        $allFaculties = $this->facultyService->all();
        $garantFacultyIDs = $request->user()
            ->garantProfile
            ?->faculties()
            ->select('faculties.id', 'faculties.name')
            ->get() ?? collect();

        if (count($garantFacultyIDs) === 0) {
            return response()->json([
                'message' => __('garant.NO_DEFAULT_FACULTY'),
                'faculties' => $allFaculties
            ]);
        }

        $faculties = [];

        foreach ($allFaculties as $faculty) {
            $faculties[] = [
                'id' => $faculty->id,
                'name' => $faculty->name,
                'active' => $faculty->active,
                'created_at' => $faculty->created_at,
                'updated_at' => $faculty->updated_at,
                'selected' => $garantFacultyIDs->contains('id', $faculty->id)
            ];
        }

        return response()->json([
            'message' => __('garant.FACULTIES_RETRIEVED_SUCCESSFULLY'),
            'faculties' => $faculties
        ]);
    }

    public function saveMyFaculties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'faculty_ids'   => 'required|array',
            'faculty_ids.*' => 'integer|exists:faculties,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('garant.FACULTIES_SAVE_VALIDATION_ERROR'),
                'errors'  => $validator->errors()
            ], 422);
        }

        $garantProfile = $request->user()->garantProfile;

        if (!$garantProfile)
            return response()->json([
                'message' => __('global_error.SERVER_ERROR')
            ], 500);


        $garantProfile->faculties()->sync($request->input('faculty_ids'));

        return response()->json([
            'message' => __('garant.FACULTIES_SAVED_SUCCESSFULLY'),
            'selected_faculties' => $garantProfile->faculties()->get()
        ]);
    }
}
