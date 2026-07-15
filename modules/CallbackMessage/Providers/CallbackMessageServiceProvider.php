<?php

namespace Modules\CallbackMessage\Providers;

use Illuminate\Support\ServiceProvider;

class CallbackMessageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->app->register(RouteServiceProvider::class);
    }
}
