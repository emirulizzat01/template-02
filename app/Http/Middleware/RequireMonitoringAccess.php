<?php

namespace App\Http\Middleware;

use App\Monitoring\MonitoringAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMonitoringAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(MonitoringAccess::class)->demo() && ! $request->user()) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
