<?php
namespace App\StripeIntegration\Webhook\Handler;

use Stripe\Event;

interface SubscriptionEventHandlerInterface
{
    public function handle(Event $event): void;
}
