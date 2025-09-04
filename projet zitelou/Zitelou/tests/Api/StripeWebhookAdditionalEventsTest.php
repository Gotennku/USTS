<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\SubscriptionPlan;
use App\Entity\Subscription;
use App\Entity\User;
use App\Enum\SubscriptionStatus;
use App\Entity\Payment;

class StripeWebhookAdditionalEventsTest extends DatabaseWebTestCase
{
    private function post(array $payload): void
    {
        $this->client->request('POST', '/api/stripe/webhook', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($payload));
    }

    public function testSubscriptionUpdatedRenewed(): void
    {
        $user = (new User())->setEmail('srenew@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())->setName('Upd')->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR');
        $sub = (new Subscription())
            ->setUser($user)
            ->setPlan($plan)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartDate(new \DateTimeImmutable())
            ->setStripeSubscriptionId('sub_update');
        $this->em->persist($user); $this->em->persist($plan); $this->em->persist($sub); $this->em->flush();

        $futureTs = (new \DateTimeImmutable('+31 days'))->getTimestamp();
        $this->post([
            'id' => 'evt_sub_upd1',
            'type' => 'customer.subscription.updated',
            'data' => [ 'object' => [ 'id' => 'sub_update', 'current_period_end' => $futureTs ] ]
        ]);
        $hist = $this->em->getRepository(\App\Entity\SubscriptionHistory::class)->findBy(['subscription' => $sub]);
        self::assertNotEmpty($hist);
        self::assertTrue(count($hist) >= 1);
    }

    public function testSubscriptionUpdatedExpired(): void
    {
        $user = (new User())->setEmail('sexp@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())->setName('Upd')->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR');
        $sub = (new Subscription())
            ->setUser($user)
            ->setPlan($plan)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartDate(new \DateTimeImmutable())
            ->setStripeSubscriptionId('sub_exp');
        $this->em->persist($user); $this->em->persist($plan); $this->em->persist($sub); $this->em->flush();

        $pastTs = (new \DateTimeImmutable('-1 day'))->getTimestamp();
        $this->post([
            'id' => 'evt_sub_upd2',
            'type' => 'customer.subscription.updated',
            'data' => [ 'object' => [ 'id' => 'sub_exp', 'current_period_end' => $pastTs ] ]
        ]);
        $subRefreshed = $this->em->getRepository(Subscription::class)->find($sub->getId());
        self::assertEquals(SubscriptionStatus::EXPIRED, $subRefreshed->getStatus());
    }

    public function testSubscriptionDeleted(): void
    {
        $user = (new User())->setEmail('sdel@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())->setName('Upd')->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR');
        $sub = (new Subscription())
            ->setUser($user)
            ->setPlan($plan)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartDate(new \DateTimeImmutable())
            ->setStripeSubscriptionId('sub_del');
        $this->em->persist($user); $this->em->persist($plan); $this->em->persist($sub); $this->em->flush();

        $this->post([
            'id' => 'evt_sub_del',
            'type' => 'customer.subscription.deleted',
            'data' => [ 'object' => [ 'id' => 'sub_del' ] ]
        ]);
        $subRefreshed = $this->em->getRepository(Subscription::class)->find($sub->getId());
        self::assertEquals(SubscriptionStatus::CANCELLED, $subRefreshed->getStatus());
    }

    public function testInvoicePaymentFailed(): void
    {
        $user = (new User())->setEmail('ifail@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())->setName('Plan')->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR');
        $sub = (new Subscription())
            ->setUser($user)->setPlan($plan)->setStatus(SubscriptionStatus::ACTIVE)->setStartDate(new \DateTimeImmutable())->setStripeSubscriptionId('sub_fail');
        $this->em->persist($user); $this->em->persist($plan); $this->em->persist($sub); $this->em->flush();

        $this->post([
            'id' => 'evt_inv_failed',
            'type' => 'invoice.payment_failed',
            'data' => [ 'object' => [ 'subscription' => 'sub_fail', 'payment_intent' => 'pi_fail_1', 'currency' => 'eur' ] ]
        ]);
        $p = $this->em->getRepository(Payment::class)->findOneBy(['stripePaymentIntentId' => 'pi_fail_1']);
        self::assertNotNull($p);
        self::assertEquals(0.0, $p->getAmount());
    }
}
