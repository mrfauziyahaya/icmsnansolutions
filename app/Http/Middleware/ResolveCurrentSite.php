<?php

namespace App\Http\Middleware;

use App\Services\SiteManager;
use Closure;
use Illuminate\Http\Request;

/**
 * Sets the active site from the request host, so everything downstream
 * (gateway credentials, Turnstile keys, branding) uses the right values.
 * Unknown hosts fall back to the default site.
 */
class ResolveCurrentSite
{
    public function __construct(private SiteManager $sites) {}

    public function handle(Request $request, Closure $next)
    {
        $this->sites->use($this->sites->keyForHost($request->getHost()));

        // Handy in views: @if(site()->key() === 'reniu')
        view()->share('currentSite', $this->sites->key());

        return $next($request);
    }
}
