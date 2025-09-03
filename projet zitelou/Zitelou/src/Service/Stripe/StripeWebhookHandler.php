<?php

namespace App\Service\Stripe;

use App\Entity\Payment;
use App\Entity\StripeWebhookLog;
use App\Entity\Subscription;
use App\Entity\SubscriptionHistory;
use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Enum\PaymentStatus;
use App\Enum\SubscriptionEvent;
use App\Enum\SubscriptionStatus;
use App\Stripe\StripeClientFactory;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;
use Stripe\Webhook;

class StripeWebhookHandler
{
    public function __construct(
        private readonly StripeClientFactory $factory,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function constructEvent(string $payload, string $signature): Event
    {
        $secret = $this->factory->getWebhookSecret();
        if ($secret) {
            return Webhook::constructEvent($payload, $signature, $secret);
        }
        // Pas de vérification si secret absent (en dev)
        return Event::constructFrom(json_decode($payload, true) ?? []);
    }

    public function handle(Event $event, StripeWebhookLog $log): void
    {
        match ($event->type) {
            'customer.subscription.created' => $this->onSubscriptionCreated($event, $log),
            'customer.subscription.updated' => $this->onSubscriptionUpdated($event, $log),
            'customer.subscription.deleted' => $this->onSubscriptionCancelled($event, $log),
            'invoice.payment_succeeded' => $this->onInvoicePaymentSucceeded($event, $log),
            'invoice.payment_failed' => $this->onInvoicePaymentFailed($event, $log),
            default => null,
        };
        $log->setProcessed(true);
    }

    private function onSubscriptionCreated(Event $event, StripeWebhookLog $log): void
    {
        $object = $event->data['object'] ?? [];
        $stripeId = $object['id'] ?? null;
        if (!$stripeId) {
            return;
        }
        $metadata = $object['metadata'] ?? [];
        $userId = isset($metadata['user_id']) ? (int)$metadata['user_id'] : null;
        $planId = isset($metadata['plan_id']) ? (int)$metadata['plan_id'] : null;
        if (!$userId || !$planId) {
            return;
        }

        $user = $this->em->getRepository(User::class)->find($userId);
        $plan = $this->em->getRepository(SubscriptionPlan::class)->find($planId);
        if (!$user || !$plan) {
            return;
        }

        // Vérifier si déjà existante (idempotence)
        $existing = $this->em->getRepository(Subscription::class)->findOneBy(['stripeSubscriptionId' => $stripeId]);
        if ($existing) {
            return;
        }

        $subscription = new Subscription();
        $subscription->setUser($user)
            ->setPlan($plan)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartDate(new DateTimeImmutable())
            ->setStripeSubscriptionId($stripeId);
        $this->em->persist($subscription);
        $this->history($subscription, SubscriptionEvent::CREATED);
    }

    private function onSubscriptionUpdated(Event $event, StripeWebhookLog $log): void
    {
        $data = $event->data['object'] ?? [];
        $stripeId = $data['id'] ?? null;
        if (!$stripeId) {
            return;
        }
        $repo = $this->em->getRepository(Subscription::class);
        /** @var Subscription|null $subscription */
        $subscription = $repo->findOneBy(['stripeSubscriptionId' => $stripeId]);
        if (!$subscription) {
            return;
        }
        $periodEnd = isset($data['current_period_end']) ? (new DateTimeImmutable())->setTimestamp((int)$data['current_period_end']) : null;
        if ($periodEnd) {
            // Renouvellement si la date a changé et est future
            if ($periodEnd > new DateTimeImmutable()) {
                // On ne modifie pas status si déjà active
                $this->history($subscription, SubscriptionEvent::RENEWED);
            } else {
                $subscription->setStatus(SubscriptionStatus::EXPIRED)->setEndDate(new DateTime());
                $this->history($subscription, SubscriptionEvent::EXPIRED);
            }
        }
    }

    private function onSubscriptionCancelled(Event $event, StripeWebhookLog $log): void
    {
        $data = $event->data['object'] ?? [];
        $stripeId = $data['id'] ?? null;
        if (!$stripeId) {
            return;
        }
        $repo = $this->em->getRepository(Subscription::class);
        /** @var Subscription|null $subscription */
        $subscription = $repo->findOneBy(['stripeSubscriptionId' => $stripeId]);
        if (!$subscription) {
            return;
        }
        $subscription->setStatus(SubscriptionStatus::CANCELLED)->setEndDate(new DateTime());
        $this->history($subscription, SubscriptionEvent::CANCELLED);
    }

    private function onInvoicePaymentSucceeded(Event $event, StripeWebhookLog $log): void
    {
        $object = $event->data['object'] ?? [];
        $lines = $object['lines']['data'] ?? [];
        $subscriptionId = $object['subscription'] ?? null;
        $amountPaid = isset($object['amount_paid']) ? ((int)$object['amount_paid']) / 100 : null; // cents -> float
        $currency = $object['currency'] ?? 'eur';
        if (!$subscriptionId || $amountPaid === null) {
            return;
        }
        /** @var Subscription|null $subscription */
        $subscription = $this->em->getRepository(Subscription::class)->findOneBy(['stripeSubscriptionId' => $subscriptionId]);
        if (!$subscription) {
            return;
        }
        // Vérifier idempotence via charge/payment_intent
        $paymentIntentId = $object['payment_intent'] ?? null;
        if (!$paymentIntentId) {
            return;
        }
        $existing = $this->em->getRepository(Payment::class)->findOneBy(['stripePaymentIntentId' => $paymentIntentId]);
        if ($existing) {
            return;
        }
        $payment = new Payment();
        $payment->setSubscription($subscription)
            ->setAmount($amountPaid)
            ->setCurrency(strtoupper($currency))
            ->setStatus(PaymentStatus::SUCCEEDED)
            ->setStripePaymentIntentId($paymentIntentId);
        $this->em->persist($payment);
    }

    private function onInvoicePaymentFailed(Event $event, StripeWebhookLog $log): void
    {
        $object = $event->data['object'] ?? [];
        $subscriptionId = $object['subscription'] ?? null;
        $paymentIntentId = $object['payment_intent'] ?? null;
        if (!$subscriptionId || !$paymentIntentId) {
            return;
        }
        /** @var Subscription|null $subscription */
        $subscription = $this->em->getRepository(Subscription::class)->findOneBy(['stripeSubscriptionId' => $subscriptionId]);
        if (!$subscription) {
            return;
        }
        // Enregistrer un payment failed seulement s'il n'existe pas encore
        $existing = $this->em->getRepository(Payment::class)->findOneBy(['stripePaymentIntentId' => $paymentIntentId]);
        if ($existing) {
            return;
        }
        $payment = new Payment();
        $payment->setSubscription($subscription)
            ->setAmount(0.0)
            ->setCurrency(strtoupper($object['currency'] ?? 'EUR'))
            ->setStatus(PaymentStatus::FAILED)
            ->setStripePaymentIntentId($paymentIntentId);
        $this->em->persist($payment);
    }

    private function history(Subscription $subscription, SubscriptionEvent $event): void
    {
        $h = new SubscriptionHistory();
        $h->setSubscription($subscription)
            ->setStatus($subscription->getStatus())
            ->setEvent($event);
        $this->em->persist($h);
    }
}
