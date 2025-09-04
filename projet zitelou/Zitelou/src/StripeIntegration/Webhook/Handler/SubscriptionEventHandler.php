<?php
namespace App\StripeIntegration\Webhook\Handler;

use App\Entity\Subscription;
use App\Entity\SubscriptionHistory;
use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Enum\SubscriptionEvent as SEvt;
use App\Enum\SubscriptionStatus;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;
use DateTimeImmutable;
use DateTime;

class SubscriptionEventHandler implements SubscriptionEventHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function handle(Event $event): void
    {
    $raw = $event->data['object'] ?? null;
    $object = $raw instanceof \Stripe\StripeObject ? $raw->toArray() : (is_array($raw) ? $raw : []);
        $stripeId = $object['id'] ?? null;
        if (!$stripeId) { return; }
        $type = $event->type;
        if ($type === 'customer.subscription.created') {
            $this->created($object, $stripeId);
        } elseif ($type === 'customer.subscription.updated') {
            $this->updated($object, $stripeId);
        } elseif ($type === 'customer.subscription.deleted') {
            $this->deleted($object, $stripeId);
        }
    }

    private function created(array $object, string $stripeId): void
    {
        $metadata = $object['metadata'] ?? [];
        $userId = (int)($metadata['user_id'] ?? 0); $planId = (int)($metadata['plan_id'] ?? 0);
        if (!$userId || !$planId) { return; }
        $user = $this->em->getRepository(User::class)->find($userId);
        $plan = $this->em->getRepository(SubscriptionPlan::class)->find($planId);
        if (!$user || !$plan) { return; }
        if ($this->em->getRepository(Subscription::class)->findOneBy(['stripeSubscriptionId' => $stripeId])) { return; }
        $sub = (new Subscription())
            ->setUser($user)
            ->setPlan($plan)
            ->setStatus(SubscriptionStatus::ACTIVE)
            ->setStartDate(new DateTimeImmutable())
            ->setStripeSubscriptionId($stripeId);
        $this->em->persist($sub);
        $this->history($sub, SEvt::CREATED);
    }

    private function updated(array $data, string $stripeId): void
    {
        $repo = $this->em->getRepository(Subscription::class);
        $sub = $repo->findOneBy(['stripeSubscriptionId' => $stripeId]);
        if (!$sub) { return; }
        $periodEnd = isset($data['current_period_end']) ? (new DateTimeImmutable())->setTimestamp((int)$data['current_period_end']) : null;
        if ($periodEnd) {
            if ($periodEnd > new DateTimeImmutable()) {
                $this->history($sub, SEvt::RENEWED);
            } else {
                $sub->setStatus(SubscriptionStatus::EXPIRED)->setEndDate(new DateTime());
                $this->history($sub, SEvt::EXPIRED);
            }
        }
    }

    private function deleted(array $data, string $stripeId): void
    {
        $repo = $this->em->getRepository(Subscription::class);
        $sub = $repo->findOneBy(['stripeSubscriptionId' => $stripeId]);
        if (!$sub) { return; }
        $sub->setStatus(SubscriptionStatus::CANCELLED)->setEndDate(new DateTime());
        $this->history($sub, SEvt::CANCELLED);
    }

    private function history(Subscription $sub, SEvt $evt): void
    {
        $h = new SubscriptionHistory();
        $h->setSubscription($sub)->setStatus($sub->getStatus())->setEvent($evt);
        $this->em->persist($h);
    }
}
