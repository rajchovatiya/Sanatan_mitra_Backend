<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Content language from the Accept-Language header (en, hi or gu); English otherwise. */
class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = substr((string) $request->header('Accept-Language'), 0, 2);
        app()->setLocale(in_array($lang, config('sanatan.languages'), true) ? $lang : 'en');

        return $next($request);
    }
}
