<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\SubscriptionPlan;
use App\Entity\Subscription;
use App\Entity\User;
use App\Enum\SubscriptionStatus;

/**
 * Remplacement du test legacy supprimé: vérifie qu'un event subscription.created crée la subscription et est idempotent.
 */
class StripeWebhookLegacyReplacementTest extends DatabaseWebTestCase
{
    private function post(array $payload): void
    {
        $this->client->request('POST', '/api/stripe/webhook', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($payload));
    }

    public function testSubscriptionCreatedAndIdempotent(): void
    {
        $user = (new User())->setEmail('legacy_repl@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())
            ->setName('LegacyPlan')
            ->setDurationDays(15)
            ->setPrice('4.99')
            ->setCurrency('EUR');
        $this->em->persist($user); $this->em->persist($plan); $this->em->flush();

        $payload = [
            'id' => 'evt_legacy_repl',
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_legacy_repl',
                    'metadata' => ['user_id' => $user->getId(), 'plan_id' => $plan->getId()]
                ]
            ]
        ];

        $this->post($payload);
        $this->post($payload); // duplicate

        $subs = $this->em->getRepository(Subscription::class)->findBy(['stripeSubscriptionId' => 'sub_legacy_repl']);
        self::assertCount(1, $subs);
        self::assertEquals(SubscriptionStatus::ACTIVE, $subs[0]->getStatus());
    }
}
