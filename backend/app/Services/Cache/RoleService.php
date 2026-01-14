<?php

namespace App\Services\Cache;

use App\Models\Role;

class RoleService extends BaseCacheService
{
    protected function key(): string
    {
        return 'roles.all';
    }

    protected function query()
    {
        return Role::all();
    }

}
