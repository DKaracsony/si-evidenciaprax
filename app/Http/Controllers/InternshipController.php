<?php

namespace App\Http\Controllers;

use App\Models\CompanyOwnerProfile;
use App\Models\Internship;
use App\Models\InternshipStatusHistory;
use App\Models\Status;
use App\Models\User;
use App\Services\Cache\InternshipStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\InternshipAgreementPdfService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InternshipController extends Controller
{
    public function __construct(
        private readonly InternshipAgreementPdfService $pdfService,
    ) {}

    public function index(Request $request)
    {
        // Na frontende v zozname potrebujeme vypísať status, firmu, semester
        $user = $request->user();

        $internships = Internship::query()
            ->where('student_profile_id', $user->studentProfile->id)
            ->with([
                'company.address.country',
                'academicYear',
                'internshipStatusHistories.status',
                'documents.status',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Internship $internship) {
                $latestStatusHistory = $internship->internshipStatusHistories
                    ? $internship->internshipStatusHistories
                        ->sortByDesc('status_changed_at')
                        ->first()
                    : null;

                return [
                    'id'           => $internship->id,
                    'start_date'   => $internship->start_date,
                    'date_to'      => $internship->date_to,
                    'description'  => $internship->description,
                    'is_draft'     => $internship->is_draft,
                    'submitted_at' => $internship->submitted_at,

                    'created_at'   => $internship->created_at,

                    // firma
                    'company' => $internship->company ? [
                        'id'          => $internship->company->id,
                        'name'        => $internship->company->name,
                        'description' => $internship->company->description,
                        'website'     => $internship->company->website,
                        'address'     => $internship->company->address,
                    ] : null,

                    // semester
                    'semester' => $internship->academicYear ? [
                        'id'         => $internship->academicYear->id,
                        'season'     => $internship->academicYear->season,
                        'start_date' => $internship->academicYear->start_date,
                        'end_date'   => $internship->academicYear->end_date,
                    ] : null,

                    // posledný status z internship_status_histories
                    'status' => $latestStatusHistory ? [
                        'name'       => $latestStatusHistory->status?->name,
                        'changed_at' => $latestStatusHistory->status_changed_at,
                    ] : null,

                    // dokumenty
                    'documents' => $internship->documents
                        ? $internship->documents->map(function ($doc) {
                            return [
                                'id'        => $doc->id,
                                'file_name' => $doc->file_name,
                                'type'      => $doc->type,
                                'status'    => $doc->status?->decision,
                            ];
                        })->values()
                        : [],
                ];
            });

        return response()->json($internships);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $request = $request->merge(['student_profile_id' => $user->studentProfile->id]);

        //predbezna validacia kde skontrolujeme iba is_draft a podla toho vieme ako postupovat dalej
        $validationData = $this->isValidatedCreationRequest($request);

        if($request->input('is_draft'))
            return $this->handleDraft($request, $user, $validationData);
        else
            return $this->handleSubmit($request, $user, $validationData);
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
            'semester' => [
                'id' => $internship->academicYear->id,
                'season' => $internship->academicYear->season,
                'start_date' => $internship->academicYear->start_date,
                'end_date' => $internship->academicYear->end_date,
            ],
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
    public function downloadAgreementPdf(Request $request, Internship $internship)
    {
        $user = $request->user();

        if (!$user->studentProfile) {
            return response()->json([
                'message' => 'Tento obsah nie je dostupný pre váš účet.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($internship->student_profile_id !== $user->studentProfile->id) {
            return response()->json([
                'message' => 'Tento obsah nie je dostupný pre váš účet.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $pdfContent = $this->pdfService->generateFor($internship);
        } catch (\RuntimeException $e) {
            Log::warning(
                'PDF generation failed for internship '.$internship->id.': '.$e->getMessage(),
                ['internship_id' => $internship->id]
            );

            return response()->json([
                'message' => 'PDF dohody nie je momentálne dostupné.',
            ], Response::HTTP_CONFLICT);
        } catch (\Throwable $e) {
            Log::error(
                'Unexpected error during PDF generation for internship '.$internship->id.': '.$e->getMessage(),
                ['internship_id' => $internship->id]
            );

            return response()->json([
                'message' => 'Pri generovaní PDF došlo k chybe.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $fileName = sprintf('dohoda-praxe-%d.pdf', $internship->id);

        return response($pdfContent, Response::HTTP_OK, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function isValidatedCreationRequest($request){
        $draftRules = [
            'is_draft' => 'required|boolean',
        ];

        $validator = Validator::make($request->all(), $draftRules);
        if ($validator->fails()) {
            return [false, $validator->errors()];
        }

        //ak draft, tak uz viac netreba validovat
        if($request->input('is_draft')){
            return [true, null];
        }

        $rules = [
            'start_date' => 'required|date',
            'date_to' => 'required|date|after_or_equal:start_date',
            'description' => 'required|string',
            'company_id' => 'required|integer|exists:companies,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'student_profile_id' => 'required|integer|exists:student_profiles,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return [false, $validator->errors()];

        return [true, null];
    }

    private function handleDraft(Request $request, $user, $validationData)
    {
        if(!$validationData[0])
            return response()->json(['message' => __('internship.MISSING_DRAFT_REQUIRED_FIELD'),], 422);

        $data = [
            'start_date'       => $request->input('start_date'),
            'date_to'          => $request->input('date_to'),
            'description'      => $request->input('description'),
            'is_draft'         => $request->boolean('is_draft', true),
            'company_id'       => $request->input('company_id'),
            'academic_year_id' => $request->input('academic_year_id'),
        ];

        // najprv zistime ci request obsahuje internship_id, ak ano, tak sa jedna o update existujuceho draftu
        if ($request->filled('internship_id')) {
            $internship = Internship::where('id', $request->input('internship_id'))
                ->where('student_profile_id', $user->studentProfile->id)
                ->first();

            if (!$internship) { // OSOBNY NAZOR - PETO: TOTO JE NAJHORSI MOZNY PRIPAD
                return response()->json([
                    'message' => __('internship.INTERNSHIP_NOT_FOUND_FOR_UPDATE'),
                ], 404);
            }

            //AK NAJDEME, TAK UROBIME UPDATE
            try {
                $internship->update($data);
            } catch (\Exception $_) {
                return response()->json([
                    'message' => __('global_error.SERVER_ERROR'),
                ], 500);
            }

            return response()->json([
                'message'    => __('internship.INTERNSHIP_UPDATED_SUCCESSFULLY'),
                'internship' => $internship,
            ]);
        }
        // opacny pripad, vytvorime novy draft
        else {
            try {
                $internship = Internship::create(array_merge($data, [
                    'student_profile_id' => $user->studentProfile->id,
                ]));
            } catch (\Exception $e) {
                return response()->json([
                    'message' => __('global_error.SERVER_ERROR'),
                ], 500);
            }

            return response()->json([
                'message'    => __('internship.DRAFT_SAVED'),
                'internship' => $internship,
            ], 201);
        }
    }

    private function handleSubmit(Request $request, $user, $validationData)
    {
        //VSETKY VALIDACIE PREBEHLI USPEŠNE?
        if (!$validationData[0]) {
            return response()->json([
                'message' => __('internship.INVALID_INTERNSHIP_DATA'),
                'errors'  => $validationData[1],
            ], 422);
        }

        $data = [
            'start_date'       => $request->input('start_date'),
            'date_to'          => $request->input('date_to'),
            'description'      => $request->input('description'),
            'is_draft'         => false,
            'company_id'       => $request->input('company_id'),
            'academic_year_id' => $request->input('academic_year_id'),
            'submitted_at'     => now(),
        ];

        $isUpdate = $request->filled('internship_id');

        $pdfBase64 = null;
        $pdfFileName = null;

        try {
            DB::beginTransaction();

            //SKONTROLUJEME CI NEEXISTUJE UZ PRACTICE -- > AK ANO, TAK UPDATE + SUBMITTED_AT a STATUS CREATED
            //TEDA Z FRONTENDU MUSI PRIST internship_id
            if ($isUpdate) {
                $internship = Internship::where('id', $request->input('internship_id'))
                    ->where('student_profile_id', $user->studentProfile->id)
                    ->first();

                if (!$internship) {
                    DB::rollBack();

                    return response()->json([
                        'message' => __('internship.INTERNSHIP_NOT_FOUND_FOR_UPDATE'),
                    ], 404);
                }

                $internship->update($data);
            } else
                $internship = Internship::create(array_merge($data, ['student_profile_id' => $user->studentProfile->id,]));

            $statusService = new InternshipStatusService();
            $statusId = $statusService->all()
                ->where('name', Status::CREATED)
                ->pluck('id')
                ->first();

            InternshipStatusHistory::create([
                'internship_id'      => $internship->id,
                'status_id'          => $statusId,
                'status_changed_at'  => now(),
                'changed_by_user_id' => $user->id,
            ]);

            $pdfBinary = $this->pdfService->generateFor($internship);
            $pdfBase64 = base64_encode($pdfBinary);
            $pdfFileName = 'dohoda-o-praxi-' . $internship->id . '.pdf';

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('global_error.SERVER_ERROR'),
                'error'   => $e->getMessage(),
            ], 500);
        }

        $statusCode = $isUpdate ? 200 : 201;

        $responseData = [
            'message'    => __('internship.INTERNSHIP_SUBMITTED_SUCCESSFULLY'),
            'internship' => $internship,
        ];

        if ($pdfBase64 !== null) {
            $responseData['agreement_pdf_base64'] = $pdfBase64;
            $responseData['agreement_file_name']  = $pdfFileName;
        }

        return response()->json($responseData, $statusCode);
    }

    public function changeStatus(Request $request, $to)
    {
        $rules = [
            'is_positive'   => 'required|boolean',
            'note'          => 'nullable|string',
            'internship_id' => 'required|integer|exists:internships,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json([
                'message' => __('internship.INVALID_STATUS_CHANGE_DATA'),
                'errors'  => $validator->errors(),
            ], 422);


        $isPositive = $request->boolean('is_positive');

        switch($to){
            case 'acceptance':
                $statusChangeTo = $isPositive ? Status::ACCEPTED : Status::REJECTED;
                break;
            default:
                return response()->json([
                    'message' => __('global_error.SERVER_ERROR'),
                ], 500);
        }

        $statusService = new InternshipStatusService();
        $statusId = $statusService->all()
            ->where('name', $statusChangeTo)
            ->pluck('id')
            ->first();

        if(!$statusId){
            return response()->json([
                'message' => __('global_error.SERVER_ERROR'),
            ], 500);
        }

        //TRANSITION IS ALLOWED?
        $internship = Internship::where('id', $request->input('internship_id'))->with('internshipStatusHistories')->first();
        $lastStatusId = $internship->internshipStatusHistories->sortByDesc('status_changed_at')->first()->status->id;
        if($lastStatusId && $lastStatusId === $statusId){
            return response()->json([
                'message' => __('internship.ALREADY_IN_DESIRED_STATUS'),
            ], 400);
        }

        if($lastStatusId){
            $lastStatus = $statusService->all()->where('id', $lastStatusId)->first();
            $newStatus = $statusService->all()->where('id', $statusId)->first();

            if($newStatus->order_index < $lastStatus->order_index)
                return response()->json([
                    'message' => __('internship.STATUS_CHANGE_NOT_ALLOWED'),
                ], 422);
        }

        InternshipStatusHistory::create([
            'internship_id'      => $request->input('internship_id'),
            'status_id'          => $statusId,
            'status_changed_at'  => now(),
            'explanation'        => $request->input('note'),
            'changed_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => __('internship.INTERNSHIP_STATUS_UPDATED_SUCCESSFULLY'),
        ], 200);
    }

}
