<?php

namespace App\Providers;

use App\Services\CountryService;
use App\Services\RoleService;
use App\Services\FacultyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
    public function boot(RoleService $roles, FacultyService $faculties, CountryService $countries): void
    {
        //CACHE VRSTVA
        if (!Cache::has(RoleService::CACHE_KEY)) {
            $roles->warm();
        }

        if (!Cache::has(FacultyService::CACHE_KEY)) {
            $faculties->warm();
        }

        if (!Cache::has(CountryService::CACHE_KEY)) {
            $countries->warm();
        }

        //PASSPORT NASTAVENIA
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(Carbon::now()->addHours(1));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(7));
        Passport::personalAccessTokensExpireIn(Carbon::now()->addMonths(6));
    }
}
