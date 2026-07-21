<?php

use Illuminate\Support\Facades\Route;
use Modules\Sale\Http\Controllers\CartController;
use Modules\Sale\Http\Controllers\CheckoutController;
use Modules\Sale\Http\Controllers\OrderController;
use Modules\Sale\Http\Controllers\Admin\AdminOrderController;

// Public/Customer routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::get('/data', [CartController::class, 'getCartData'])->name('data');
    Route::post('/add', [CartController::class, 'addItem'])->name('add');
    Route::put('/item/{itemId}', [CartController::class, 'updateItem'])->name('update');
    Route::delete('/item/{itemId}', [CartController::class, 'removeItem'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/email', [CheckoutController::class, 'processEmail'])->name('email');
    Route::get('/shipping', [CheckoutController::class, 'shipping'])->name('shipping');
    Route::post('/shipping', [CheckoutController::class, 'processShipping'])->name('process.shipping');
    Route::get('/payment', [CheckoutController::class, 'payment'])->name('payment');
    Route::post('/payment', [CheckoutController::class, 'processPayment'])->name('process.payment');
    Route::get('/confirmation/{orderId}', [CheckoutController::class, 'confirmation'])->name('confirmation');
});

// User order routes (requires authentication)
Route::middleware(['auth'])->prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{orderId}', [OrderController::class, 'show'])->name('show');
    Route::post('/{orderId}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    Route::get('/{orderId}/track', [OrderController::class, 'track'])->name('track');
});

// Admin routes
// Route::middleware(['auth', 'admin'])->prefix('admin/orders')->name('admin.orders.')->group(function () {
//     Route::get('/', [AdminOrderController::class, 'index'])->name('index');
//     Route::get('/{orderId}', [AdminOrderController::class, 'show'])->name('show');
//     Route::put('/{orderId}/status', [AdminOrderController::class, 'updateStatus'])->name('update.status');
//     Route::post('/{orderId}/tracking', [AdminOrderController::class, 'addTracking'])->name('add.tracking');
// });