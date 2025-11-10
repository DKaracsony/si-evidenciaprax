<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Cache;

class CountryService
{
    public const CACHE_KEY = 'countries.all';

    /**
     * Retrieve all countries, cached indefinitely.
     */
    public function all()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () =>
        Country::select('id', 'name', 'icon')
            ->orderBy('name')
            ->get()
        );
    }

    /**
     * Clear the cached countries list.
     */
    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Warm (preload) the cache.
     */
    public function warm(): void
    {
        $this->all();
    }
}
