<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\Cache\RoleService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function searchStudentByName(Request $request)
    {
        if(!isGarant($request->user()))
            return response()->json([
                'message' => __('global_error.UNAUTHORIZED'),
            ], 403);

        $name = trim((string) $request->get('q', ''));

        if ($name === '') {
            return response()->json([
                'message' => 'Query parameter "q" is required.',
            ], 422);
        }

        $roleService = new RoleService();
        $studentRoleId = $roleService->all()->firstWhere('name', Role::STUDENT)->id;

        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $students = User::query()
            ->where('role_id', $studentRoleId)
            ->where(function ($q) use ($name, $parts) {

                $q->where('first_name', 'LIKE', "%{$name}%")
                    ->orWhere('last_name', 'LIKE', "%{$name}%");

                if (count($parts) >= 2) {
                    $first = $parts[0];
                    $last  = $parts[1];

                    $q->orWhere(function ($qq) use ($first, $last) {
                        $qq->where('first_name', 'LIKE', "%{$first}%")
                            ->where('last_name',  'LIKE', "%{$last}%");
                    })
                        ->orWhere(function ($qq) use ($first, $last) {
                            $qq->where('first_name', 'LIKE', "%{$last}%")
                                ->where('last_name',  'LIKE', "%{$first}%");
                        });
                }
            })
            ->select('id', 'first_name', 'last_name')
            ->limit(20)
            ->get();

        return response()->json(['students' => $students], 200);
    }
}
