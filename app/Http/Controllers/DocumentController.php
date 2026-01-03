<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Internship;
use Illuminate\Support\Facades\Storage;
use App\Models\DocumentStatus;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\ReviewReportRequest;

class DocumentController extends Controller
{
    public function uploadAgreement(DocumentRequest $request)
    {
        $user = $request->user();
        $internshipId = $request->integer('internship_id');
        $file = $request->file('document');
        $filename = $file->getClientOriginalName();

        $existingInternshipForUser = Internship::where('id', $internshipId)
            ->whereHas('studentProfile.user', fn ($q) => $q->whereKey($user->id))
            ->exists();

        if (!$existingInternshipForUser)
            return response()->json([
                'message' => __('document.INTERNHIP_NOT_FOUND_FOR_USER'),
            ], 404);

        try{
            $path = Storage::disk('internship_agreement')->putFileAs(
                $internshipId,
                $file,
                $filename
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('document.AGREEMENT_UPLOAD_FAIL'),
            ], 500);
        }

        $document = Document::updateOrCreate(
            [
                'internship_id' => $internshipId,
                'type' => Document::TYPE_AGREEMENT,
                'uploaded_by_user_id' => $user->id,
            ],
            [
                'file_name' => $filename,
            ]
        );

        if (!$document->wasRecentlyCreated) { // pri aktualizacii zmazeme stare subory
            $files = Storage::disk('internship_agreement')->files($internshipId);
            foreach ($files as $filePath) {
                if (basename($filePath) !== $filename) {
                    Storage::disk('internship_agreement')->delete($filePath);
                }
            }
        }

        return response()->json([
            'message' => $document->wasRecentlyCreated
                ? __('document.AGREEMENT_UPLOAD_SUCCESS')
                : __('document.AGREEMENT_UPDATE_SUCCESS'),
            'document' => $document,
            'file_path' => $path
        ]);
    }

    public function uploadReport(DocumentRequest $request, Internship $internship)
    {
        $user = $request->user();
        $file = $request->file('document');
        $filename = $file->getClientOriginalName();

        $actor = $this->resolveInternshipAccessActor($user, $internship);
        if ($actor === null) {
            return response()->json([
                'message' => __('document.INTERNHIP_NOT_FOUND_FOR_USER'),
            ], 404);
        }

        $decision = $actor === 'company' ? 'approved' : 'pending';
        $status = DocumentStatus::where('decision', $decision)->first();

        if (!$status) {
            return response()->json([
                'message' => "Document status not configured (missing decision: {$decision})",
            ], 500);
        }

        $existing = Document::where('internship_id', $internship->id)
            ->where('type', Document::TYPE_STATEMENT)
            ->first();

        try {
            $path = Storage::disk('reports')->putFileAs(
                $internship->id,
                $file,
                $filename
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('document.REPORT_UPLOAD_FAIL'),
            ], 500);
        }

        $document = Document::updateOrCreate(
            [
                'internship_id' => $internship->id,
                'type' => Document::TYPE_STATEMENT,
            ],
            [
                'file_name' => $filename,
                'uploaded_by_user_id' => $user->id,
                'document_status_id' => $status->id,
            ]
        );

        if ($existing && $existing->file_name !== $filename) {
            $files = Storage::disk('reports')->files($internship->id);

            foreach ($files as $filePath) {
                if (basename($filePath) !== $filename) {
                    Storage::disk('reports')->delete($filePath);
                }
            }
        }

        return response()->json([
            'message'   => $document->wasRecentlyCreated
                ? __('document.REPORT_UPLOAD_SUCCESS')
                : __('document.REPORT_UPDATE_SUCCESS'),
            'document'  => $document->load(['status', 'uploadedByUser']),
            'file_path' => $path,
        ], 200);
    }

    public function downloadDocument(Document $document): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $user = request()->user();

        $hasAccess = hasInternshipRelationToLoggedUser($user, $document->internship_id);

        if (!$hasAccess) {
            return response()->json([
                'message' => __('document.DO_NOT_HAVE_PERMISSION_TO_DOWNLOAD_DOCUMENT'),
            ], 403);
        }

        $disk = match ($document->type) {
            Document::TYPE_AGREEMENT => 'internship_agreement',
            Document::TYPE_STATEMENT => 'reports',
            default => null,
        };

        if ($disk === null) {
            return response()->json([
                'message' => __('document.DOCUMENT_TYPE_NOT_SUPPORTED'),
            ], 400);
        }

        $relativePath = $document->internship_id . '/' . $document->file_name;

        if (!Storage::disk($disk)->exists($relativePath)) {
            return response()->json([
                'message' => __('document.DOCUMENT_FILE_NOT_FOUND'),
            ], 404);
        }

        return Storage::disk($disk)->download($relativePath, $document->file_name);
    }

    public function reviewReport(ReviewReportRequest $request, Document $document)
    {
        $user = $request->user();

        if ($document->type !== Document::TYPE_STATEMENT) {
            return response()->json([
                'message' => __('document.ONLY_REPORT_CAN_BE_REVIEWED'),
            ], 400);
        }

        $internship = $document->internship()->first();

        if (!$internship) {
            return response()->json([
                'message' => __('document.INTERNSHIP_NOT_FOUND'),
            ], 404);
        }

        $actor = $this->resolveInternshipAccessActor($user, $internship);
        if ($actor !== 'company') {
            return response()->json([
                'message' => __('document.DO_NOT_HAVE_PERMISSION_TO_REVIEW_REPORT'),
            ], 403);
        }

        $decision = $request->string('decision')->toString(); // approved | rejected
        $note = $request->string('note')->toString();

        $newStatus = DocumentStatus::create([
            'decision' => $decision,
            'note' => $note,
            'reviewer_user_id' => $user->id,
            // created_at auto (useCurrent)
        ]);

        $document->document_status_id = $newStatus->id;
        $document->save();

        return response()->json([
            'message' => $decision === 'approved'
                ? __('document.REPORT_APPROVED_SUCCESS')
                : __('document.REPORT_REJECTED_SUCCESS'),
            'document' => $document->fresh()->load(['status', 'uploadedByUser']),
        ], 200);
    }

    public function getInternshipDocuments($internshipId)
    {
        $user = request()->user();
        $hasAccess = hasInternshipRelationToLoggedUser($user, $internshipId);

        if (!$hasAccess) {
            return response()->json([
                'message' => __('document.DO_NOT_HAVE_PERMISSION_TO_LIST_DOCUMENTS'),
            ], 403);
        }

        $documents = Document::where('internship_id', $internshipId)->with(['status', 'uploadedByUser'])->get();

        return response()->json([
            'message' => $documents->isEmpty()
                ? __('document.NO_DOCUMENTS_FOUND')
                : __('document.DOCUMENTS_RETRIEVED_SUCCESSFULLY'),
            'documents' => $documents,
        ]);
    }
    private function resolveInternshipAccessActor($user, Internship $internship): ?string
    {
        $studentProfileId = $user->studentProfile?->id;

        if ($studentProfileId && (int) $internship->student_profile_id === (int) $studentProfileId) {
            return 'student';
        }

        $companyId = $user->companyOwnerProfile?->company?->id;

        if ($companyId && (int) $internship->company_id === (int) $companyId) {
            return 'company';
        }

        return null;
    }
}
