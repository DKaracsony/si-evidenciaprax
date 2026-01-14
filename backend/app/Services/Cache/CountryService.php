<?php

namespace App\Services\Cache;

use App\Models\Country;

class CountryService extends BaseCacheService
{
    protected function key(): string
    {
        return 'countries.all';
    }

    protected function query()
    {
        return Country::select('id', 'name', 'icon')
            ->orderBy('name')
            ->get();
    }
}
