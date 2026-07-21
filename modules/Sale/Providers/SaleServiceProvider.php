<?php

namespace Modules\Sale\Providers;

use Illuminate\Support\ServiceProvider;

class SaleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->app->register(RouteServiceProvider::class);
    }
}