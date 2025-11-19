<?php

namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use App\Models\User;
use Carbon\Carbon;

class UpdateLastLoginTime
{

    public function handle(AccessTokenCreated $event)
    {
        if ($event->userId)
            User::where('id', $event->userId)->update(['last_login_at' => Carbon::now()]);
    }
}
