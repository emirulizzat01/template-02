<?php

use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/sign-in', [SessionController::class, 'create'])->name('login');
    Route::post('/sign-in', [SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::post('/sign-out', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
