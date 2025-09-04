<?php
namespace App\StripeIntegration\Webhook\Idempotency;

interface EventIdempotencyCheckerInterface
{
    public function isAlreadyProcessed(string $eventId): bool;
    public function markProcessed(string $eventId, string $type, array $objectPayload): void;
}
