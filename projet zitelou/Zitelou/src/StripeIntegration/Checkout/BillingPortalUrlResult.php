<?php

namespace App\StripeIntegration\Checkout;

class BillingPortalUrlResult
{
    public function __construct(public readonly string $url) {}
}
