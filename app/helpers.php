<?php

use App\Services\SiteManager;

if (! function_exists('site')) {
    /**
     * The active site (multi-domain). Handy in Blade and drivers:
     *   site()->key()                        // 'nansolutions' | 'reniu'
     *   site()->label()
     *   site()->gatewayConfig('chip', 'api_key')
     */
    function site(): SiteManager
    {
        return app(SiteManager::class);
    }
}
