<?php

namespace App\Tests\Entity;

use App\Entity\{Subscription, Payment, SubscriptionHistory, StripeWebhookLog, SubscriptionPlan};

class SubscriptionAggregateTest extends DatabaseTestCase
{
    public function testSubscriptionWithPaymentsHistoryAndWebhookLogs(): void
    {
        $user = EntityFactory::user();
        $plan = EntityFactory::subscriptionPlan();
        $sub = EntityFactory::subscription($user, $plan);
        $payment1 = EntityFactory::payment($sub, 1);
        $payment2 = EntityFactory::payment($sub, 2);
        $history = EntityFactory::subscriptionHistory($sub, 1);
        $webhook = EntityFactory::stripeWebhookLog($sub, 'invoice.paid');

        $this->em->persist($user);
        $this->em->persist($plan);
        foreach ([$sub,$payment1,$payment2,$history,$webhook] as $e) { $this->em->persist($e); }
        $this->em->flush();
        $id = $sub->getId();
        $this->em->clear();

        $reloaded = $this->em->getRepository(Subscription::class)->find($id);
        self::assertNotNull($reloaded);
        self::assertInstanceOf(SubscriptionPlan::class, $reloaded->getPlan());
        self::assertCount(2, $reloaded->getPayments());
        self::assertCount(1, $reloaded->getHistoryEntries());
        self::assertCount(1, $reloaded->getWebhookLogs());
    }
}
