<?php

namespace App\StripeIntegration\Checkout;

class CheckoutSessionInput
{
    public function __construct(
        public readonly string $userId,
        public readonly string $planId,
        public readonly string $successUrl,
        public readonly string $cancelUrl,
    ) {}
}
