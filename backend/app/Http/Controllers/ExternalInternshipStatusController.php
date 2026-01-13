<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\InternshipStatusHistory;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ExternalInternshipStatusController extends Controller
{
    public function changeStatus(Request $request, Internship $internship)
    {
        $clientId = optional($request->user()?->token())->client_id;

        $validator = Validator::make($request->all(), [
            'to'          => ['required', 'string', 'in:acceptance,approval,defense'],
            'explanation' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatusId = $internship->internshipStatusHistories()
            ->latest('status_changed_at')
            ->value('status_id');

        if ($validator->fails()) {
            Log::channel('external_system')->info('unsuccess', [
                'internship_id'   => $internship->id,
                'old_status_id'   => $oldStatusId,
                'new_status_id'   => null,
                'oauth_client_id' => $clientId,
                'ip'              => $request->ip(),
                'at'              => now()->toIso8601String(),
                'errors'          => $validator->errors()->toArray(),
            ]);

            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();

        $statusMap = [
            'acceptance' => 'Potvrdená',
            'approval'   => 'Schválená',
            'defense'    => 'Obhájená',
        ];

        try {
            return DB::transaction(function () use (
                $request,
                $internship,
                $data,
                $statusMap,
                $clientId,
                $oldStatusId
            ) {

                $newStatus = Status::where('name', $statusMap[$data['to']])->first();

                if (!$newStatus) {
                    Log::channel('external_system')->info('unsuccess', [
                        'internship_id'   => $internship->id,
                        'old_status_id'   => $oldStatusId,
                        'new_status_id'   => null,
                        'oauth_client_id' => $clientId,
                        'ip'              => $request->ip(),
                        'at'              => now()->toIso8601String(),
                        'error'           => 'Target status not found in DB',
                    ]);

                    return response()->json([
                        'message' => 'Target status not found.',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($oldStatusId === $newStatus->id) {
                    Log::channel('external_system')->info('unsuccess', [
                        'internship_id'   => $internship->id,
                        'old_status_id'   => $oldStatusId,
                        'new_status_id'   => $newStatus->id,
                        'oauth_client_id' => $clientId,
                        'ip'              => $request->ip(),
                        'at'              => now()->toIso8601String(),
                        'error'           => 'Status already set',
                    ]);

                    return response()->json([
                        'message' => 'Status already set.',
                        'internship_id' => $internship->id,
                        'status_id' => $newStatus->id,
                    ], Response::HTTP_OK);
                }

                InternshipStatusHistory::create([
                    'internship_id'       => $internship->id,
                    'status_id'           => $newStatus->id,
                    'explanation'         => $data['explanation'] ?? null,
                    'status_changed_at'   => now(),
                    'changed_by_user_id'  => $request->user()->id,
                ]);

                Log::channel('external_system')->info('success', [
                    'internship_id'   => $internship->id,
                    'old_status_id'   => $oldStatusId,
                    'new_status_id'   => $newStatus->id,
                    'oauth_client_id' => $clientId,
                    'ip'              => $request->ip(),
                    'at'              => now()->toIso8601String(),
                ]);

                return response()->json([
                    'message'        => 'Status successfully changed.',
                    'internship_id'  => $internship->id,
                    'old_status_id'  => $oldStatusId,
                    'new_status_id'  => $newStatus->id,
                ], Response::HTTP_OK);
            });
        } catch (\Throwable $e) {
            Log::channel('external_system')->error('unsuccess', [
                'internship_id'   => $internship->id,
                'old_status_id'   => $oldStatusId,
                'new_status_id'   => null,
                'oauth_client_id' => $clientId,
                'ip'              => $request->ip(),
                'at'              => now()->toIso8601String(),
                'exception'       => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Internal server error.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
