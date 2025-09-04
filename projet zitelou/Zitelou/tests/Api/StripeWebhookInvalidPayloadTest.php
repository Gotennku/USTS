<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Component\HttpFoundation\Response;

class StripeWebhookInvalidPayloadTest extends DatabaseWebTestCase
{
    public function testInvalidSignatureReturns400(): void
    {
        // On remplace la factory pour retourner un secret => le contrôleur utilise StripeWebhook::constructEvent
        // La signature envoyée est volontairement invalide, ce qui doit provoquer une exception et un 400.
        $factory = new class extends \App\Stripe\StripeClientFactory {
            public function __construct() {}
            public function create(): \Stripe\StripeClient { return new \Stripe\StripeClient(['api_key' => 'sk_test_dummy', 'stripe_version' => '2024-06-20']); }
            public function getWebhookSecret(): ?string { return 'whsec_test_secret'; }
        };
        static::getContainer()->set(\App\Stripe\StripeClientFactory::class, $factory);

        $this->client->request('POST', '/api/stripe/webhook', server: [
            'CONTENT_TYPE' => 'application/json',
            // Stripe en-tête normalement 'Stripe-Signature', sous forme server param => HTTP_STRIPE_SIGNATURE
            'HTTP_STRIPE_SIGNATURE' => 't=12345,v1=invalid'
        ], content: json_encode(['id' => 'evt_invalid', 'type' => 'test.event']));

        $response = $this->client->getResponse();
        self::assertSame(400, $response->getStatusCode(), $response->getContent());
        self::assertStringContainsString('Invalid', $response->getContent());
    }
}
