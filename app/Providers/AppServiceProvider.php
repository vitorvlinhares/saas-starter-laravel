<?php

namespace App\Providers;

use App\Models\Team;
use App\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One tenant context per request / queued job.
        $this->app->scoped(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Subscriptions belong to teams, not users.
        Cashier::useCustomerModel(Team::class);
    }
}
