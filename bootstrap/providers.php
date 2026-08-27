<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

use Modules\User\Providers\UserServiceProvider;
use Modules\Product\Providers\ProductServiceProvider;
use Modules\ContactMessage\Providers\ContactMessageServiceProvider;
use Modules\Delivery\Providers\DeliveryServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,

    UserServiceProvider::class,
    ProductServiceProvider::class,
    ContactMessageServiceProvider::class,
    DeliveryServiceProvider::class,
];
