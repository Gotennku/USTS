<?php

namespace App\StripeIntegration\Checkout;

class CheckoutSessionResult
{
    public function __construct(
        public readonly string $url,
    ) {}
}
