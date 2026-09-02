<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictShopToTesting
{
    /**
     * The wine module intentionally has no public mode. Keeping the route
     * registered lets Comgate use its callback during an enabled test, while
     * disabled installations behave as though the route did not exist.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('shop.testing_enabled'), 404);

        return $next($request);
    }
}
