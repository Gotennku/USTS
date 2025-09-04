<?php

namespace App\StripeIntegration\Port;

interface PlanPriceProviderInterface
{
    /** Return Stripe price id or null if not linked. */
    public function getPriceId(string $planId): ?string;
}
