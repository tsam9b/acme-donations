<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Services\SettingsService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the payment interface to a dummy implementation for now
        $this->app->bind(\App\Payments\PaymentInterface::class, function ($app) {
            return new \App\Payments\DummyPayment();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Share settings globally with Inertia so components can access preferences easily
        Inertia::share([
            'settings' => function () {
                return SettingsService::getAll();
            },
            'predefinedAmounts' => function () {
                return SettingsService::getPredefinedAmounts();
            }
        ]);
    }
}
