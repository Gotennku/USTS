<?php

namespace App\StripeIntegration\Adapter;

use App\Entity\SubscriptionPlan;
use App\StripeIntegration\Port\PlanPriceProviderInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrinePlanPriceProvider implements PlanPriceProviderInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function getPriceId(string $planId): ?string
    {
        /** @var SubscriptionPlan|null $plan */
        $plan = $this->em->getRepository(SubscriptionPlan::class)->find($planId);
        return $plan?->getStripePriceId();
    }
}
