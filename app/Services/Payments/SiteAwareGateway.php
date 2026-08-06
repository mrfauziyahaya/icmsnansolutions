<?php

namespace App\Services\Payments;

/**
 * A driver whose credentials depend on which site (domain) the payment
 * belongs to. The manager binds the site before handing the driver back.
 */
interface SiteAwareGateway
{
    public function usingSite(?string $site): static;

    public function site(): string;
}
