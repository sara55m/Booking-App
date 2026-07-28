<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $language = strtolower($request->header('Accept-Language', 'en'));

        $locale = str_starts_with($language, 'ar')
        ? 'ar'
        : 'en';

        if (in_array($locale, config('app.supported_locales', ['en', 'ar']))) {
            app()->setLocale($locale);
        }

        return $next($request);

    }
}
