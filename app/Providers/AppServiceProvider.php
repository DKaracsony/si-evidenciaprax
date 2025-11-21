<?php

namespace App\Providers;

use App\Services\Cache\CountryService;
use App\Services\Cache\FacultyService;
use App\Services\Cache\RoleService;
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
        //PASSPORT NASTAVENIA
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(Carbon::now()->addHours(1));
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(7));
        Passport::personalAccessTokensExpireIn(Carbon::now()->addMonths(6));
    }
}
