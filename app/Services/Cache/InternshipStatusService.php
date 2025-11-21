<?php

namespace App\Services\Cache;

use App\Models\Status;

class InternshipStatusService extends BaseCacheService
{
    protected function key(): string
    {
        return 'internship_statuses.all';
    }

    protected function query()
    {
        return Status::all();
    }
}
