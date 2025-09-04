<?php

namespace App\StripeIntegration\Port;

interface CustomerProviderInterface
{
    /**
     * Ensure remote Stripe customer ID exists for given user.
     */
    public function ensureCustomer(string $userId, string $email): string;
}
