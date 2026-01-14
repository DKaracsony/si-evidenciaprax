<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

abstract class BaseCacheService
{
    abstract protected function key(): string;
    abstract protected function query();

    public function all()
    {
        return Cache::rememberForever($this->key(), fn () => $this->query());
    }

    public function clear(): void
    {
        Cache::forget($this->key());
    }

    public function warm(): void
    {
        $this->all();
    }
}
