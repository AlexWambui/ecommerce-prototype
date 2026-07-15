<?php

use Illuminate\Support\Facades\Route;

use Modules\CallbackMessage\Http\Controllers\CallbackMessageController;

Route::get('/contact', [CallbackMessageController::class, 'index'])->name('callback-messages.index');
Route::post('/contact', [CallbackMessageController::class, 'store'])->name('callback-messages.store');

Route::middleware('role:admin,super_admin')->group(function () {
    Route::get('/callback-messages', [CallbackMessageController::class, 'list_callbacks'])->name('callback-messages.list');
    Route::get('/callback-messagess/{callback_message:uuid}/edit', [CallbackMessageController::class, 'edit'])->name('callback-messages.edit');
    Route::put('/callback-messagess/{callback_message:uuid}', [CallbackMessageController::class, 'update'])->name('callback-messages.update');
    Route::patch('/{callback_message}/toggle-resolved', [CallbackMessageController::class, 'toggleResolved'])->name('callback-messages.toggle-resolved');
    Route::delete('/callback-messagess/{callback_message:uuid}', [CallbackMessageController::class, 'destroy'])->name('callback-messages.destroy');
});