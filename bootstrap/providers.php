<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Modules\User\Providers\UserServiceProvider;
use Modules\Guest\Providers\GuestServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,

    UserServiceProvider::class,
    GuestServiceProvider::class,
];
