<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\CarWashJob;
use App\Observers\UserObserver;
use App\Observers\CarWashJobObserver;

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
    public function boot(): void
    {
        User::observe(UserObserver::class);
        CarWashJob::observe(CarWashJobObserver::class);

        // Legacy admin users (users.role = 'admin') bypass all permission checks
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->role === 'admin') {
                return true;
            }
        });
    }
}
