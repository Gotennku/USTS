<?php

namespace App\Tests\Entity;

use App\Entity\{Subscription, SubscriptionHistory};
use App\Enum\{SubscriptionEvent, SubscriptionStatus};

class SubscriptionStatusTransitionTest extends DatabaseTestCase
{
    public function testBasicStatusTransitionsAndHistorySnapshot(): void
    {
        $user = EntityFactory::user();
        $plan = EntityFactory::subscriptionPlan();
        $sub = EntityFactory::subscription($user, $plan);
        $this->em->persist($user);
        $this->em->persist($plan);
        $this->em->persist($sub);
        $this->em->flush();

        // Simuler expiration
        $sub->setStatus(SubscriptionStatus::EXPIRED);
        $hist1 = new SubscriptionHistory();
        $hist1->setSubscription($sub)->setStatus($sub->getStatus())->setEvent(SubscriptionEvent::EXPIRED);
        $this->em->persist($hist1);

        // Simuler renouvellement
        $sub->setStatus(SubscriptionStatus::ACTIVE);
        $hist2 = new SubscriptionHistory();
        $hist2->setSubscription($sub)->setStatus($sub->getStatus())->setEvent(SubscriptionEvent::RENEWED);
        $this->em->persist($hist2);
        $this->em->flush();
        $id = $sub->getId();
        $this->em->clear();

        /** @var Subscription $reloaded */
        $reloaded = $this->em->getRepository(Subscription::class)->find($id);
        self::assertNotNull($reloaded);
        self::assertEquals(SubscriptionStatus::ACTIVE, $reloaded->getStatus());
        self::assertCount(2, $reloaded->getHistoryEntries(), 'Deux entrées d\'historique attendues.');

        // Vérifie cohérence status vs events enregistrés
        $events = [];
        foreach ($reloaded->getHistoryEntries() as $h) {
            $events[] = $h->getEvent()->value.'|'.$h->getStatus()->value;
        }
        self::assertContains('expired|expired', $events);
        self::assertContains('renewed|active', $events);
    }
}
