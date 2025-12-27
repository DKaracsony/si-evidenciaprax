<?php

namespace App\Providers;

use App\Models\DocumentStatus;
use App\Models\InternshipStatusHistory;
use App\Observers\DocumentStatusObserver;
use App\Observers\InternshipStatusHistoryObserver;
use App\Services\Cache\CountryService;
use App\Services\Cache\FacultyService;
use App\Services\Cache\RoleService;
use Carbon\Carbon;
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

        //OBSERVERI
        InternshipStatusHistory::observe(InternshipStatusHistoryObserver::class);
        DocumentStatus::observe(DocumentStatusObserver::class);
    }
}
