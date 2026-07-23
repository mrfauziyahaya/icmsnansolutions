<?php

namespace App\Http\Middleware;

use App\Services\SiteManager;
use Closure;
use Illuminate\Http\Request;

/**
 * Keeps each domain to its own surface area. reniu.my only exposes the
 * checkout and its webhooks; anything else 404s rather than quietly serving
 * NAN Solutions content (landing page, admin, lookup, …) on the wrong brand.
 */
class EnsureRouteAllowedForSite
{
    public function __construct(private SiteManager $sites) {}

    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()?->getName();

        if (! $this->sites->allowsRoute($routeName)) {
            // Send the bare domain to its checkout instead of a dead 404.
            if ($request->path() === '/' && $this->sites->allowsRoute('pay.create')) {
                return redirect()->route('pay.create');
            }

            abort(404);
        }

        return $next($request);
    }
}
