<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetSiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $isEnglishSite = in_array($host, config('international.english_hosts', []), true);
        $locale = $isEnglishSite ? 'en' : 'cs';

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        view()->share('siteLocale', $locale);
        view()->share('isEnglishSite', $isEnglishSite);

        return $next($request);
    }
}
