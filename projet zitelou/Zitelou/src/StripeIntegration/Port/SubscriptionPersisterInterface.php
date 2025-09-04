<?php

namespace App\StripeIntegration\Port;

use DateTimeImmutable;

class SubscriptionDTO
{
    public function __construct(
        public readonly string $stripeId,
        public readonly string $userId,
        public readonly string $planId,
        public readonly string $status,
        public readonly ?DateTimeImmutable $currentPeriodEnd,
    ) {}
}

interface SubscriptionPersisterInterface
{
    public function upsert(SubscriptionDTO $dto): void;
}
