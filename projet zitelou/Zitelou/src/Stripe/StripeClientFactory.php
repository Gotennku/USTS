<?php

namespace App\Stripe;

use Stripe\StripeClient;

/**
 * Fabrique centralisant l'instanciation du client Stripe.
 */
class StripeClientFactory
{
    public function __construct(
        private readonly string $secretKey,
        private readonly ?string $webhookSecret = null,
    ) {
    }

    public function create(): StripeClient
    {
        return new StripeClient([
            'api_key' => $this->secretKey,
            'stripe_version' => '2024-06-20',
        ]);
    }

    public function getWebhookSecret(): ?string
    {
        return $this->webhookSecret;
    }
}
