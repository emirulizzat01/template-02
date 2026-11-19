<?php

use App\Http\Middleware\RequireMonitoringAccess;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware(RequireMonitoringAccess::class)->group(function (): void {
    if (config('template.modules.monitoring')) {
        require __DIR__.'/monitoring.php';
    } else {
        Route::view('/', 'home')->name('dashboard');
    }

    if (config('template.modules.examples')) {
        require __DIR__.'/examples.php';
    }
});
