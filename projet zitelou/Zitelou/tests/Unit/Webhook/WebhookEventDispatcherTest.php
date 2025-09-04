<?php

namespace App\Tests\Unit\Webhook;

use App\StripeIntegration\Webhook\WebhookEventDispatcher;
use App\StripeIntegration\Webhook\Handler\SubscriptionEventHandlerInterface;
use App\StripeIntegration\Webhook\Handler\InvoiceEventHandlerInterface;
use App\StripeIntegration\Webhook\Idempotency\EventIdempotencyCheckerInterface;
use PHPUnit\Framework\TestCase;
use Stripe\Event;

class WebhookEventDispatcherTest extends TestCase
{
    public function testDispatchSkipsAlreadyProcessed(): void
    {
        $subscriptionH = $this->createMock(SubscriptionEventHandlerInterface::class);
        $invoiceH = $this->createMock(InvoiceEventHandlerInterface::class);
        $idem = $this->createMock(EventIdempotencyCheckerInterface::class);

        $idem->method('isAlreadyProcessed')->willReturn(true);
        $subscriptionH->expects(self::never())->method('handle');
        $invoiceH->expects(self::never())->method('handle');
        $idem->expects(self::never())->method('markProcessed');

        $dispatcher = new WebhookEventDispatcher($subscriptionH, $invoiceH, $idem);
        $event = Event::constructFrom(['id' => 'evt_1', 'type' => 'customer.subscription.created', 'data' => ['object' => []]]);
        $dispatcher->dispatch($event);
        self::assertTrue(true); // pas d'exception
    }

    public function testDispatchRoutesSubscription(): void
    {
        $subscriptionH = $this->createMock(SubscriptionEventHandlerInterface::class);
        $invoiceH = $this->createMock(InvoiceEventHandlerInterface::class);
        $idem = $this->createMock(EventIdempotencyCheckerInterface::class);

        $idem->method('isAlreadyProcessed')->willReturn(false);
        $subscriptionH->expects(self::once())->method('handle');
        $invoiceH->expects(self::never())->method('handle');
        $idem->expects(self::once())->method('markProcessed');

        $dispatcher = new WebhookEventDispatcher($subscriptionH, $invoiceH, $idem);
        $event = Event::constructFrom(['id' => 'evt_2', 'type' => 'customer.subscription.updated', 'data' => ['object' => []]]);
        $dispatcher->dispatch($event);
    }

    public function testDispatchRoutesInvoice(): void
    {
        $subscriptionH = $this->createMock(SubscriptionEventHandlerInterface::class);
        $invoiceH = $this->createMock(InvoiceEventHandlerInterface::class);
        $idem = $this->createMock(EventIdempotencyCheckerInterface::class);

        $idem->method('isAlreadyProcessed')->willReturn(false);
        $subscriptionH->expects(self::never())->method('handle');
        $invoiceH->expects(self::once())->method('handle');
        $idem->expects(self::once())->method('markProcessed');

        $dispatcher = new WebhookEventDispatcher($subscriptionH, $invoiceH, $idem);
        $event = Event::constructFrom(['id' => 'evt_3', 'type' => 'invoice.payment_succeeded', 'data' => ['object' => []]]);
        $dispatcher->dispatch($event);
    }
}
