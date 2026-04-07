<?php

namespace App\Providers;

use App\Models\CarWashJob;
use App\Models\User;
use App\Observers\CarWashJobObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
    public function boot(): void
    {
        // When app runs in a subdirectory, make route()/url() use request root so links/AJAX don't 404.
        // Only run when request is available (web request) to avoid 500 during bootstrap.
        try {
            if (! app()->runningInConsole() && app()->bound('request')) {
                $req = request();
                if ($req && method_exists($req, 'root')) {
                    $root = $req->root();
                    if (is_string($root) && $root !== '') {
                        \Illuminate\Support\Facades\URL::forceRootUrl(rtrim($root, '/'));
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore to prevent 500
        }

        User::observe(UserObserver::class);
        CarWashJob::observe(CarWashJobObserver::class);

        // Log warning when a single request runs too many queries (dev only)
        if (config('app.debug')) {
            \DB::listen(function ($query) {
                static $count = 0;
                $count++;
                if ($count === 30) {
                    \Log::warning('HIGH QUERY COUNT: 30+ queries on '.request()->path());
                }
            });
        }

        // Legacy admin users (users.role = 'admin') bypass all permission checks
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->role === 'admin') {
                return true;
            }
        });
    }
}
