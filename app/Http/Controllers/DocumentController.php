<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Models\Document;
use App\Models\Internship;
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
}
