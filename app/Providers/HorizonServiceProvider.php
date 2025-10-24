<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class HorizonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Horizon routes (Dashboard)
        Horizon::auth(function ($request) {
            // Gate logic; tighten for prod (e.g., admin users only)
            return app()->environment('local') || Gate::allows('viewHorizon');
        });
    }
}
