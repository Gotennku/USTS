<?php

namespace App\Tests\Api;

use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Enum\SubscriptionStatus;
use App\Entity\Subscription;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookApiTest extends DatabaseWebTestCase
{
    private function post(array $payload): Response
    {
        $this->client->request('POST', '/api/stripe/webhook', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($payload));
        return $this->client->getResponse();
    }

    public function testSubscriptionCreated(): void
    {
        $user = (new User())->setEmail('u1@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())
            ->setName('Basic')
            ->setDurationDays(30)
            ->setPrice('9.99')
            ->setCurrency('EUR');
        $this->em->persist($user); $this->em->persist($plan); $this->em->flush();

        $payload = [
            'id' => 'evt_sub_create_1',
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_test_1',
                    'metadata' => ['user_id' => $user->getId(), 'plan_id' => $plan->getId()]
                ]
            ]
        ];
        $resp = $this->post($payload);
        self::assertSame(200, $resp->getStatusCode());
        $sub = $this->em->getRepository(Subscription::class)->findOneBy(['stripeSubscriptionId' => 'sub_test_1']);
        self::assertNotNull($sub);
        self::assertEquals(SubscriptionStatus::ACTIVE, $sub->getStatus());
    }

    public function testIdempotentDuplicateEvent(): void
    {
        $user = (new User())->setEmail('u2@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())
            ->setName('Pro')
            ->setDurationDays(30)
            ->setPrice('19.99')
            ->setCurrency('EUR');
        $this->em->persist($user); $this->em->persist($plan); $this->em->flush();

        $payload = [
            'id' => 'evt_sub_create_dup',
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_dup',
                    'metadata' => ['user_id' => $user->getId(), 'plan_id' => $plan->getId()]
                ]
            ]
        ];
        $this->post($payload);
        $this->post($payload); // duplicate
        $subs = $this->em->getRepository(Subscription::class)->findBy(['stripeSubscriptionId' => 'sub_dup']);
        self::assertCount(1, $subs);
    }

    public function testInvoicePaymentSucceeded(): void
    {
        // Préparer une subscription existante
        $user = (new User())->setEmail('u3@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())
            ->setName('Gold')
            ->setDurationDays(30)
            ->setPrice('29.99')
            ->setCurrency('EUR');
        $sub = (new Subscription())
            ->setUser($user)
            ->setPlan($plan)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartDate(new \DateTimeImmutable())
            ->setStripeSubscriptionId('sub_pay');
        $this->em->persist($user); $this->em->persist($plan); $this->em->persist($sub); $this->em->flush();

        $payload = [
            'id' => 'evt_inv_succ_web',
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'subscription' => 'sub_pay',
                    'payment_intent' => 'pi_web_1',
                    'amount_paid' => 2500,
                    'currency' => 'eur'
                ]
            ]
        ];
        $resp = $this->post($payload);
        self::assertSame(200, $resp->getStatusCode());
        $payments = $this->em->getRepository(\App\Entity\Payment::class)->findBy(['stripePaymentIntentId' => 'pi_web_1']);
        self::assertCount(1, $payments);
        self::assertEquals(25.0, $payments[0]->getAmount());
    }
}
