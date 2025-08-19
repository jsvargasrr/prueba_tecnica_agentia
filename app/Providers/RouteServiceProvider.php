<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

public function boot(): void
{
    RateLimiter::for('uploads', function (Request $request) {
        $key = optional($request->user())->id ?: $request->ip();
        return [Limit::perMinute(6)->by('up:'.$key)]; // máx 6/min 
    });

    RateLimiter::for('queries', function (Request $request) {
        $key = optional($request->user())->id ?: $request->ip();
        return [Limit::perMinute(30)->by('q:'.$key)];
    });
}
