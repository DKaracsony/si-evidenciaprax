<?php

namespace App\Services;

use App\Models\Faculty;
use Illuminate\Support\Facades\Cache;

class FacultyService
{
    public const CACHE_KEY = 'faculties.all';

    public function all()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => Faculty::all());
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
