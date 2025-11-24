<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\InternshipStatusHistory;
use App\Models\Status;
use App\Services\Cache\InternshipStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Internship::query()
            ->where('student_profile_id', $user->studentProfile->id);

        // Filtrovanie podľa statusu: ?status=approved
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filtrovanie podľa akademického roka: ?academic_year_id=3
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', (int) $request->get('academic_year_id'));
        }

        // Dátumové filtrovanie:
        if ($request->filled('from')) {
            $query->whereDate('date_from', '>=', $request->get('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date_to', '<=', $request->get('to'));
        }

        $internships = $query
            ->orderByDesc('created_at')
            ->get();

        return response()->json($internships);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $studentProfileId = $user->studentProfile->id;
        $request = $request->merge(['student_profile_id' => $studentProfileId]);

        $rules = [
            'start_date' => 'required|date',
            'date_to' => 'required|date|after_or_equal:start_date',
            'description' => 'required|string',
            'is_draft' => 'required|boolean',
            'company_id' => 'required|integer|exists:companies,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'student_profile_id' => 'required|integer|exists:student_profiles,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json([
                'message' => __('internship.INTERNSHIP_CREATE_FAILED'),
                'errors'  => $validator->errors()->toArray(),
            ], 422);

        try{
            $internship = Internship::create([
                'student_profile_id' => $studentProfileId,
                'start_date' => $request->input('start_date'),
                'date_to' => $request->input('date_to'),
                'description' => $request->input('description'),
                'is_draft' => $request->input('is_draft') ?? false,
                'company_id' => $request->input('company_id'),
                'academic_year_id' => $request->input('academic_year_id'),
                'submitted_at' => $request->input('is_draft') ? null : now(),
            ]);

            if($internship && !$request->input('is_draft')){
                $statusService = new InternshipStatusService();
                $statusId = $statusService->all()->where('name', Status::CREATED)->pluck('id')->first();

                InternshipStatusHistory::create([
                    'internship_id' => $internship->id,
                    'status_id' => $statusId,
                    'status_changed_at' => now(),
                    'changed_by_user_id' => $user->id,
                ]);
            }
        }
        catch (\Exception $e){
            return response()->json([
                'message' => __('global_error.SERVER_ERROR'),
            ], 500);
        }

        return response()->json($internship, 201);
    }
}
