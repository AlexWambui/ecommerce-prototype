<?php

namespace Modules\Support\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Modules\ContactMessage\Providers\ContactMessageServiceProvider;
use Modules\Delivery\Providers\DeliveryServiceProvider;
use Modules\Expense\Providers\ExpenseServiceProvider;
use Modules\Order\Providers\OrderServiceProvider;
use Modules\Payment\Providers\PaymentServiceProvider;
use Modules\Product\Providers\ProductServiceProvider;
use Modules\Storefront\Providers\StorefrontServiceProvider;
use Modules\Support\Http\Middleware\RoleMiddleware;
use Modules\User\Providers\UserServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(UserServiceProvider::class);
        $this->app->register(UserServiceProvider::class);
        $this->app->register(ProductServiceProvider::class);
        $this->app->register(ContactMessageServiceProvider::class);
        $this->app->register(DeliveryServiceProvider::class);
        $this->app->register(OrderServiceProvider::class);
        $this->app->register(PaymentServiceProvider::class);
        $this->app->register(ExpenseServiceProvider::class);
        $this->app->register(StorefrontServiceProvider::class);
        // $this->app->register(\Modules\StoreBranch\Providers\BranchServiceProvider::class);
    }

    public function boot(Router $router): void
    {
        // Register 'role' middleware alias globally
        $router->aliasMiddleware('role', RoleMiddleware::class);
    }
}
