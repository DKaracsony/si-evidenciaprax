<?php

namespace App\Providers;

use App\Services\Cache\CountryService;
use App\Services\Cache\FacultyService;
use App\Services\Cache\InternshipStatusService;
use App\Services\Cache\PermissionService;
use App\Services\Cache\RoleService;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('cache.services', function ($app) {
            return [
                $app->make(FacultyService::class),
                $app->make(RoleService::class),
                $app->make(CountryService::class),
                $app->make(InternshipStatusService::class),
                $app->make(PermissionService::class)
            ];
        });
    }

    public function boot()
    {
        foreach ($this->app->make('cache.services') as $service) {
            $service->warm();
        }
    }
}
