<?php

namespace App\Http\Controllers;

use App\Models\CompanyOwnerProfile;
use App\Models\Internship;
use App\Models\InternshipStatusHistory;
use App\Models\Status;
use App\Models\User;
use App\Services\Cache\InternshipStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InternshipController extends Controller
{
    public function index(Request $request)
    {
        //TODO: Atus - doplnit firmu, dokumenty, semester, status z tabulky internship_status_history a filtraciu NEpotrebujeme
        //na frontende v zozname potrebujem vypisat status, firmu, semester
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
        //TODO: is_draft handling - update or save
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

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $internship = Internship::where('id', $id)
            ->where('student_profile_id', $user->studentProfile->id)
            ->with(['company', 'academicYear', 'internshipStatusHistories.status'])
            ->first();

        if (!$internship)
            return response()->json([
                'message' => __('internship.INTERNSHIP_NOT_FOUND'),
            ], 404);

        $company_profile = null;
        if ($internship->company && $internship->company->id)
            $company_profile = CompanyOwnerProfile::where('id', $internship->company->id)->with('user')->first();

        $data = [
            'id' => $internship->id,
            'start_date' => $internship->start_date,
            'date_to' => $internship->date_to,
            'description' => $internship->description,
            'is_draft' => $internship->is_draft,
            'submitted_at' => $internship->submitted_at,
            'company' => $internship->company ? [
                'id' => $internship->company->id,
                'name' => $internship->company->name,
                'description' => $internship->company->description,
                'website' => $internship->company->website,
                'address' => $internship->company->address->with('country')->first(),
                'contact_person' => [
                    'id' => $company_profile->user->id,
                    'first_name' => $company_profile->user->first_name,
                    'last_name' => $company_profile->user->last_name,
                    'title_before' => $company_profile->user->title_before,
                    'title_after' => $company_profile->user->title_after,
                    'email' => $company_profile->user->email,
                ]
            ] : null,
            'semester' => $internship->acedemicYear ? [
                'id' => $internship->academicYear->id,
                'season' => $internship->academicYear->season,
                'start_date' => $internship->academicYear->start_date,
                'end_date' => $internship->academicYear->end_date,
            ] : null,
            'status_history' => $internship->internshipStatusHistories ? [
                $internship->internshipStatusHistories->sortByDesc('status_changed_at')->map(function ($history) {
                    return [
                        'status' => $history->status->name,
                        'explanation' => $history->explanation,
                        'status_changed_at' => $history->status_changed_at,
                        'changed_by_user => ' => [
                            'id' => $history->changedByUser->id,
                            'first_name' => $history->changedByUser->first_name,
                            'last_name' => $history->changedByUser->last_name,
                            'title_before' => $history->changedByUser->title_before,
                            'title_after' => $history->changedByUser->title_after,
                            'email' => $history->changedByUser->email,
                        ],
                    ];
                }),
            ] : null
        ];

        return response()->json($data);
    }
}
