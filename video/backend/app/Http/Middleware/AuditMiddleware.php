<?php

namespace App\Http\Middleware;

use App\Events\ActivityLogSavedEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $activity = $request->url();

        $properties = [
            'ip_address' => $request->ip(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'path' => $request->path(),
            'params' => $request->query->all(),
            'data' => $request->all(),
        ];

        $user = $request->user('api');

        $activityLog = activity()
            ->inLog('api')
            ->causedBy($user)
            ->withProperties($properties)
            ->event($activity)
            ->log($request->method());

        event(new ActivityLogSavedEvent($activityLog));

        return $next($request);
    }
}

