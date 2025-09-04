<?php

namespace App\StripeIntegration\Checkout;

interface CheckoutServiceInterface
{
    public function createSubscriptionCheckout(CheckoutSessionInput $input): CheckoutSessionResult;
}
