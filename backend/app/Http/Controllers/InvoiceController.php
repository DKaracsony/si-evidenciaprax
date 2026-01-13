<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentStatus;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class InvoiceController extends Controller
{
    public function upload(Request $request, Internship $internship)
    {
        $user = $request->user();

        if (!$user?->studentProfile || $internship->student_profile_id !== $user->studentProfile->id) {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'invoice_month' => ['required', 'date_format:Y-m'],
            'replace_document_id' => ['nullable', 'integer', 'exists:documents,id'],
        ]);



        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file = $request->file('document');

        DB::beginTransaction();
        try {
            // ak ide o nahradenie
            if ($request->filled('replace_document_id')) {
                $old = Document::where('id', $request->integer('replace_document_id'))
                    ->where('internship_id', $internship->id)
                    ->where('type', Document::TYPE_INVOICE)
                    ->first();

                if ($old) {
                    // zmažeme starý súbor
                    Storage::disk('internship_salary_statements')->delete($old->file_path);
                    $old->delete();
                }
            }

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

            $document = Document::create([
                'file_name'           => $file->getClientOriginalName(),
                'file_path'           => $storedPath,
                'type'                => Document::TYPE_INVOICE,
                // uložíme mesiac automaticky (YYYY-MM-01)
                'invoice_month' => Carbon::createFromFormat('Y-m', $request->invoice_month)->startOfMonth(),
                'internship_id'       => $internship->id,
                'uploaded_by_user_id' => $user->id,
                'document_status_id'  => $status->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Server error during upload.',
            ], 500);
        }

        return response()->json([
            'message'  => 'Faktúra bola úspešne nahraná.',
            'document' => $document->load(['status', 'uploadedByUser']),
        ], 201);
    }

}
