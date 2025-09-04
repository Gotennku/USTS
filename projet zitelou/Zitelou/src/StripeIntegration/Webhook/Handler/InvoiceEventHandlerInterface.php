<?php
namespace App\StripeIntegration\Webhook\Handler;

use Stripe\Event;

interface InvoiceEventHandlerInterface
{
    public function handle(Event $event): void;
}
