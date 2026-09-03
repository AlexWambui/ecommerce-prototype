<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,

    \Modules\User\Providers\UserServiceProvider::class,
    \Modules\Product\Providers\ProductServiceProvider::class,
    \Modules\ContactMessage\Providers\ContactMessageServiceProvider::class,
    \Modules\Delivery\Providers\DeliveryServiceProvider::class,
    \Modules\Order\Providers\OrderServiceProvider::class,
    \Modules\Payment\Providers\PaymentServiceProvider::class,
    \Modules\Expense\Providers\ExpenseServiceProvider::class,
];
