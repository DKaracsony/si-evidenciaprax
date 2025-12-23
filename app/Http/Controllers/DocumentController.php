<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Internship;
use App\Models\Role;
use Illuminate\Support\Facades\Storage;

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

    public function getInternshipDocuments($internshipId)
    {
        $user = request()->user();
        $hasAccess = false;

        switch($user->role->name) {
            case Role::STUDENT:
                $hasAccess = Internship::where('id', $internshipId)
                    ->whereHas('studentProfile.user', fn ($q) => $q->whereKey($user->id))
                    ->exists();
                break;
            case Role::COMPANY:
                $hasAccess = Internship::where('id', $internshipId)
                    ->whereHas('company.ownerProfiles.user', fn ($q) => $q->whereKey($user->id))
                    ->exists();
                break;
            case Role::GARANT:
                $hasAccess = true;
                break;
        }

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
}
