<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\ProductCategoryController;

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('products', [ProductController::class, 'store'])->name('products.store');

Route::get('product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
Route::get('product-categories/create', [ProductCategoryController::class, 'create'])->name('product-categories.create');
Route::post('product-categories', [ProductCategoryController::class, 'store'])->name('product-categories.store');
