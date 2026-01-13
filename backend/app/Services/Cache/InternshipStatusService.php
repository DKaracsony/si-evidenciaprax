<?php

namespace App\Services\Cache;

use App\Models\Status;
use Illuminate\Support\Collection;

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

    /** @return Collection<int, Status> */
    public function collection(): Collection
    {
        return collect(parent::all());
    }
}
