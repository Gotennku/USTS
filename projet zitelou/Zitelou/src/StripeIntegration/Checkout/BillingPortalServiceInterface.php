<?php

namespace App\StripeIntegration\Checkout;

interface BillingPortalServiceInterface
{
    public function createPortalUrl(string $userId, string $returnUrl): BillingPortalUrlResult;
}
