<?php

namespace App\Services\Cache;

use App\Models\Faculty;

class FacultyService extends BaseCacheService
{
    protected function key(): string
    {
        return 'faculties.all';
    }

    protected function query()
    {
        return Faculty::all();
    }
}
