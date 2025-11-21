<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Services\Cache\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermission
{
    public function __construct(
        private readonly PermissionService $permissionService,
    ) {}
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $userRole = $request->user()->role;

        $userPermissionsByRole = $this->permissionService->all();
        $userPermissions = $userPermissionsByRole->all()[$userRole->id] ?? [];

        foreach ($permissions as $permission) {
            if (!in_array($permission, $userPermissions)) {
                return response()->json([
                    'message' => __('global_error.SCOPE_FORBIDDEN'),
                ], 403);
            }
        }

        return $next($request);
    }
}
