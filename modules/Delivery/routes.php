<?php

use Illuminate\Support\Facades\Route;
use Modules\Delivery\Http\Controllers\DeliveryLocationController;
use Modules\Delivery\Http\Controllers\DeliveryAreaController;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::prefix('delivery-locations')
        ->name('delivery-locations.')
        ->controller(DeliveryLocationController::class)
        ->group(function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{delivery_location:uuid}/show', 'show')->name('show');
        Route::get('/{delivery_location}/edit', 'edit')->name('edit');
        Route::put('/{delivery_location}', 'update')->name('update');
        Route::delete('/{delivery_location}', 'destroy')->name('destroy');
    });

    Route::prefix('delivery-areas')
        ->name('delivery-areas.')
        ->controller(DeliveryAreaController::class)
        ->group(function()
    {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{delivery_area}/edit', 'edit')->name('edit');
        Route::put('/{delivery_area}', 'update')->name('update');
        Route::delete('/{delivery_area}', 'destroy')->name('destroy');
    });
});