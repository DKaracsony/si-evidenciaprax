<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class RoleService{
    public const CACHE_KEY = 'roles.all';

    public function all()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Role::all());
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function warm(): void
    {
        $this->all();
    }

}
