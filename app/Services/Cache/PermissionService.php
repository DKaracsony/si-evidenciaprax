<?php

namespace App\Services\Cache;

use App\Models\Permission;
use App\Models\Role;

class PermissionService extends BaseCacheService
{
    protected function key(): string
    {
        return 'permissions_with_role.all';
    }

    //mapuje permissions do ról
    protected function query()
    {
        return Role::with('permissions:id,name')
            ->get()
            ->mapWithKeys(function ($role) {
                return [
                    $role->id => $role->permissions->pluck('name')->toArray(),
                ];
            })
            ->toArray();
    }

}
