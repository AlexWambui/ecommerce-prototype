<?php

use Illuminate\Support\Facades\Route;
use Modules\Guest\Http\Controllers\HomePageController;
use Modules\Guest\Http\Controllers\ShopPageController;

Route::get('/', [HomePageController::class, 'index'])->name('home');
Route::get('/shop', [ShopPageController::class, 'index'])->name('shop-page.index');
