<?php

use Illuminate\Support\Facades\Route;
use Modules\Guest\Http\Controllers\HomePageController;

Route::get('/', [HomePageController::class, 'index'])->name('home');