<?php

namespace App\Tests\Stub;

use App\Stripe\StripeClientFactory;
use Stripe\StripeClient;

class TestStripeClientFactory extends StripeClientFactory
{
    public function __construct() {}

    public function create(): StripeClient
    {
        // Retourne un client Stripe factice avec clés vides.
        return new StripeClient(['api_key' => 'sk_test_dummy', 'stripe_version' => '2024-06-20']);
    }

    public function getWebhookSecret(): ?string
    {
        return null; // pas de signature en test
    }
}
