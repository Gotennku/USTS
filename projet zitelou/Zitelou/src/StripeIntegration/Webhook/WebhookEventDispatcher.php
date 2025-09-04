<?php

namespace App\StripeIntegration\Webhook;

use App\StripeIntegration\Webhook\Handler\SubscriptionEventHandlerInterface;
use App\StripeIntegration\Webhook\Handler\InvoiceEventHandlerInterface;
use App\StripeIntegration\Webhook\Idempotency\EventIdempotencyCheckerInterface;
use Stripe\Event;

class WebhookEventDispatcher
{
    public function __construct(
        private readonly SubscriptionEventHandlerInterface $subscriptionHandler,
        private readonly InvoiceEventHandlerInterface $invoiceHandler,
        private readonly EventIdempotencyCheckerInterface $idempotency,
    ) {}

    public function dispatch(Event $event): void
    {
        if ($this->idempotency->isAlreadyProcessed($event->id)) {
            return; // idempotent skip
        }
        $type = $event->type;
        if (str_starts_with($type, 'customer.subscription.')) {
            $this->subscriptionHandler->handle($event);
        } elseif (str_starts_with($type, 'invoice.payment_')) {
            $this->invoiceHandler->handle($event);
        }
    $raw = $event->data['object'] ?? null;
    $payloadObject = $raw instanceof \Stripe\StripeObject ? $raw->toArray() : (is_array($raw) ? $raw : []);
    $this->idempotency->markProcessed($event->id, $type, $payloadObject);
    }
}
