<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\ProductCategoryController;

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('products', [ProductController::class, 'store'])->name('products.store');
Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

Route::get('product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
Route::get('product-categories/create', [ProductCategoryController::class, 'create'])->name('product-categories.create');
Route::post('product-categories', [ProductCategoryController::class, 'store'])->name('product-categories.store');
Route::get('product-categories/{product_category}/edit', [ProductCategoryController::class, 'edit'])->name('product-categories.edit');
Route::put('product-categories/{product_category}', [ProductCategoryController::class, 'update'])->name('product-categories.update');
Route::delete('product-categories/{product_category}', [ProductCategoryController::class, 'destroy'])->name('product-categories.destroy');
