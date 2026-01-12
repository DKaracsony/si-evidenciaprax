<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentStatus;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function upload(Request $request, Internship $internship)
    {
        $user = $request->user();

        if (!$user?->studentProfile || $internship->student_profile_id !== $user->studentProfile->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'files'   => ['required','array','min:1'],
            'files.*' => ['required','file','mimes:pdf,jpg,jpeg,png','max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $files = $request->file('files');
        $created = [];

        DB::beginTransaction();
        try {
            foreach ($files as $file) {
                $storedPath = $file->store(
                    (string) $internship->id,
                    'internship_salary_statements'
                );

                $status = DocumentStatus::create([
                    'decision' => 'pending',
                    'note' => null,
                    'reviewer_user_id' => null,
                    'created_at' => now(),
                ]);

                $doc = Document::create([
                    'file_name'           => $file->getClientOriginalName(),
                    'file_path'           => $storedPath,
                    'type'                => Document::TYPE_INVOICE,
                    'internship_id'       => $internship->id,
                    'uploaded_by_user_id' => $user->id,
                    'document_status_id'  => $status->id,
                ]);

                $created[] = $doc;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Server error during upload.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Invoices uploaded.',
            'documents' => $created,
        ], 201);
    }
}
