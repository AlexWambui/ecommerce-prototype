<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Modules\User\Providers\UserServiceProvider;
use Modules\Guest\Providers\GuestServiceProvider;
use Modules\Product\Providers\ProductServiceProvider;
use Modules\Delivery\Providers\DeliveryServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,

    UserServiceProvider::class,
    GuestServiceProvider::class,
    ProductServiceProvider::class,
    DeliveryServiceProvider::class,
];
