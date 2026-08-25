<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

use Modules\User\Providers\UserServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,

    UserServiceProvider::class,
];
