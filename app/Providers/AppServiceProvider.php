<?php

namespace App\Providers;

use App\Models\Group;
use App\Models\User;
use App\Observers\GroupObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);
        Group::observe(GroupObserver::class);
    }
}
