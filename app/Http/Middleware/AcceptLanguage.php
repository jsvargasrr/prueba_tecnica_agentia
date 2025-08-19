<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcceptLanguage
{
    public function handle(Request $request, Closure $next)
    {
        $fallback = config('app.locale', 'es');
        $header = $request->header('Accept-Language', $fallback);

        // Parsear "es-CO,es;q=0.9,en;q=0.8"
        $langs = collect(explode(',', $header))
            ->map(fn($l) => Str::of($l)->before(';')->trim()->lower())
            ->filter()
            ->values();

        $supported = collect(['es','en','pt','fr','de']); // ajustable
        $picked = $langs->first(fn($l) => $supported->contains(Str::substr($l,0,2))) ?? $fallback;

        app()->setLocale(Str::substr($picked,0,2));
        return $next($request);
    }
}
