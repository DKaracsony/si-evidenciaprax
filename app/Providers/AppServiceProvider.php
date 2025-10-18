<?php

namespace App\Providers;

use App\Services\RoleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(RoleService $roles): void
    {
        if (!Cache::has(RoleService::CACHE_KEY)) {
            $roles->warm();
        }
    }
}
