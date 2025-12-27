<?php

namespace App\Http\Controllers;

use App\Http\Resources\InternshipResource;
use App\Models\CompanyOwnerProfile;
use App\Models\Internship;
use App\Models\InternshipStatusHistory;
use App\Models\Status;
use App\Models\User;
use App\Services\Cache\InternshipStatusService;
use App\Services\InternshipQueryBuilder;
use App\Services\StatusChangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\InternshipAgreementPdfService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AcademicYear;
use Carbon\Carbon;
use App\Http\Requests\UpdateInternshipRequest;

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

    public function changeStatus(Request $request, $to) //bulk and single supported
    {
        $rules = [
            'is_positive'   => 'required|boolean',
            'note'          => 'nullable|string',

            'internship_id'  => 'nullable|integer|exists:internships,id',
            'internship_ids' => 'nullable|array|min:1',
            'internship_ids.*' => 'integer|distinct|exists:internships,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($v) use ($request) {
            $hasSingle = $request->filled('internship_id');
            $hasBulk   = is_array($request->input('internship_ids')) && count($request->input('internship_ids')) > 0;

            if (!$hasSingle && !$hasBulk)
                $v->errors()->add('internship_id', __('internship.INVALID_STATUS_CHANGE_DATA'));

            if ($hasSingle && $hasBulk)
                $v->errors()->add('internship_ids', __('internship.INVALID_STATUS_CHANGE_DATA'));
        });


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
            case 'approval':
                if(!$isPositive)
                    return response()->json(['message' => __('internship.INVALID_STATUS_CHANGE_DATA'),], 422);
                $statusChangeTo = Status::APPROVED;
                break;
            case 'defense':
                $statusChangeTo = $isPositive ? Status::DEFENDED : Status::UNDEFENDED;
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

        $failed = [];
        $updated = [];

        $ids = $request->filled('internship_id')
            ? [(int) $request->input('internship_id')]
            : array_map('intval', $request->input('internship_ids', []));

        $internships = Internship::whereIn('id', $ids)
            ->with(['internshipStatusHistories.status'])
            ->get()
            ->keyBy('id');

        $statusChangeService = new StatusChangeService();

        foreach ($ids as $id) {
            $internship = $internships->get($id);

            if (!$internship) {
                $failed[] = ['internship' => $internships->get($id), 'reason' => __('internship.INTERNSHIP_NOT_FOUND'),];
                continue;
            }

            //TRANSITION IS ALLOWED?
            $statusChangeService->setInternshipId($id);
            $statusChangeService->setNewStatusId($statusId);
            $transitionCheck = $statusChangeService->transitionAllowed();

            if ($transitionCheck !== '') {
                $failed[] = ['internship' => $internships->get($id), 'reason' => $transitionCheck];
                continue;
            }

            InternshipStatusHistory::create([
                'internship_id'      => $id,
                'status_id'          => $statusId,
                'status_changed_at'  => now(),
                'explanation'        => $request->input('note'),
                'changed_by_user_id' => $request->user()->id,
            ]);

            $updated[] = $internships->get($id);
        }

        return response()->json([
            'message' => $failed === []
                ? __('internship.INTERNSHIP_STATUS_UPDATED_SUCCESSFULLY')
                : __('internship.STATUS_CHANGED_PARTIALLY_SUCCESSFULLY'),
            'updated' => $updated,
            'failed'  => $failed,
        ], empty($failed) ? 200 : 207);
    }

    public function companyCreatedInternships(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $companyId = $validated['company_id'];

        $createdStatusId = Status::where('name', Status::CREATED)->value('id');

        // Praxe danej firmy, kde posledný status je "CREATED"
        $internships = Internship::query()
            ->where('company_id', $companyId)
            ->with(['company', 'academicYear', 'internshipStatusHistories.status'])
            ->get()
            ->filter(function (Internship $internship) use ($createdStatusId) {
                $latest = $internship->internshipStatusHistories
                    ? $internship->internshipStatusHistories
                        ->sortByDesc('status_changed_at')
                        ->first()
                    : null;

                return $latest && $latest->status_id === $createdStatusId;
            })
            ->values();

        return response()->json($internships);
    }

    public function allInternshipsWithPaginationAndFilter(Request $request){
        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100)); // max 100 na stranu

        $query = InternshipQueryBuilder::fromRequest($request);

        return InternshipResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function garantUpdateInternship(
        UpdateInternshipRequest $request,
        Internship $internship,
        InternshipStatusService $internshipStatusService
        ) {
        $user = $request->user();
        $data = $request->validated();

        $latestHistory = $internship->internshipStatusHistories()
            ->orderByDesc('status_changed_at')
            ->first();

        $currentStatusId = $latestHistory?->status_id;

        $newStatusId = $data['status_id'] ?? null;
        $rule = null;

        if ($newStatusId !== null) {
            if ($currentStatusId === null) {
                return response()->json([
                    'message' => 'Praxe nemá aktuálny status v histórii.',
                ], 422);
            }

            if ((int) $newStatusId !== (int) $currentStatusId) {
                $rule = $internshipStatusService->getTransitionRuleByIds((int) $currentStatusId, (int) $newStatusId);

                if (!$rule) {
                    return response()->json([
                        'message' => 'Zmena stavu nie je povolená.',
                    ], 422);
                }

                if (($rule['requires_explanation'] ?? false) && empty($data['note'])) {
                    return response()->json([
                        'message' => 'Pre túto zmenu stavu je potrebné zdôvodnenie.',
                    ], 422);
                }
            }
        }

        DB::beginTransaction();

        try {
            $internship->update([
                'company_id'         => $data['company_id']         ?? $internship->company_id,
                'student_profile_id' => $data['student_profile_id'] ?? $internship->student_profile_id,
                'academic_year_id'   => $data['academic_year_id']   ?? $internship->academic_year_id,
                'start_date'         => $data['start_date']         ?? $internship->start_date,
                'date_to'            => $data['date_to']            ?? $internship->date_to,
                'description'        => array_key_exists('description', $data) ? $data['description'] : $internship->description,
                'is_draft'           => $data['is_draft']           ?? $internship->is_draft,
            ]);

            if ($newStatusId !== null && $currentStatusId !== null && (int) $newStatusId !== (int) $currentStatusId) {
                InternshipStatusHistory::create([
                    'internship_id'      => $internship->id,
                    'status_id'          => (int) $newStatusId,
                    'status_changed_at'  => now(),
                    'explanation'        => $data['note'] ?? null,
                    'changed_by_user_id' => $user->id,
                ]);


            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('global_error.SERVER_ERROR'),
                'error'   => $e->getMessage(),
            ], 500);
        }

        $internship->load(['company', 'academicYear', 'internshipStatusHistories.status']);

        return response()->json([
            'message'    => 'Internship updated successfully.',
            'internship' => $internship,
        ], 200);
    }
}
