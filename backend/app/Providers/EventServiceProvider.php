<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Passport\Events\AccessTokenCreated;
use App\Listeners\UpdateLastLoginTime;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AccessTokenCreated::class => [
            UpdateLastLoginTime::class,
        ],
    ];
}
