<?php

use Illuminate\Support\Facades\Route;
use Modules\ContactMessage\Http\Controllers\ContactMessageController;
use Modules\Order\Http\Controllers\EcommerceOrderController;
use Modules\Storefront\Http\Controllers\GuestProductController;
use Modules\Storefront\Http\Controllers\HomePageController;
use Modules\Storefront\Http\Controllers\StorefrontOrderController;

Route::get('/', [HomePageController::class, 'index'])->name('home');
Route::inertia('/about', 'storefront/about/Index')->name('about-page');
Route::get('/contact', [ContactMessageController::class, 'create'])->name('contact-messages.create');
Route::post('/contact', [ContactMessageController::class, 'store'])->name('contact-messages.store');
Route::get('/shop', [GuestProductController::class, 'shop'])->name('shop-page.index');
Route::get('/product-details/{product:slug}', [GuestProductController::class, 'productDetails'])->name('product-details.index');
Route::get('/cart', [StorefrontOrderController::class, 'cart'])->name('cart.index');
Route::post('/cart/add', [StorefrontOrderController::class, 'add'])->name('cart.add');
Route::get('/cart', [StorefrontOrderController::class, 'cart'])->name('cart.index');
Route::post('/cart/update', [StorefrontOrderController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [StorefrontOrderController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [StorefrontOrderController::class, 'clear'])->name('cart.clear');
Route::get('/checkout', [StorefrontOrderController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout', [EcommerceOrderController::class, 'store'])->name('checkout.place-order');
Route::get('/checkout/success/{order:order_number}', [StorefrontOrderController::class, 'checkoutSuccess'])->name('checkout.success');
