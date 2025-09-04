<?php

namespace App\Controller\Api;

use App\StripeIntegration\Webhook\WebhookEventDispatcher;
use App\Stripe\StripeClientFactory;
use Stripe\Event;
use Stripe\Webhook as StripeWebhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/api/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
class StripeWebhookController
{
    public function __construct(
        private readonly StripeClientFactory $factory,
        private readonly WebhookEventDispatcher $dispatcher,
    ) {}

    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sig = $request->headers->get('stripe-signature');

        $secret = $this->factory->getWebhookSecret();
        try {
            if ($secret) {
                $event = StripeWebhook::constructEvent($payload, $sig ?? '', $secret);
            } else {
                $event = Event::constructFrom(json_decode($payload, true) ?? []);
            }
        } catch (Throwable) {
            return new Response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

    // Délègue totalement à la couche dispatcher + idempotence (un seul endroit crée le log)
    $this->dispatcher->dispatch($event);

        return new Response('ok');
    }
}
