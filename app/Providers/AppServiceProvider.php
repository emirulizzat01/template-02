<?php

namespace App\Providers;

use App\Http\Middleware\RequireMonitoringAccess;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', fn (Request $request): array => [
            Limit::perMinute(8)->by('login:'.hash('sha256', Str::lower((string) $request->input('email'))).'|'.$request->ip()),
            Limit::perMinute(30)->by('login-ip:'.$request->ip()),
        ]);

        Livewire::addPersistentMiddleware([RequireMonitoringAccess::class]);
    }
}
