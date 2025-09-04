<?php
namespace App\StripeIntegration\Webhook\Handler;

use App\Entity\Payment;
use App\Entity\Subscription;
use App\Enum\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;

class InvoiceEventHandler implements InvoiceEventHandlerInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function handle(Event $event): void
    {
        $type = $event->type;
    $raw = $event->data['object'] ?? null;
    $object = $raw instanceof \Stripe\StripeObject ? $raw->toArray() : (is_array($raw) ? $raw : []);
        $subscriptionId = $object['subscription'] ?? null;
        $paymentIntentId = $object['payment_intent'] ?? null;
        if (!$subscriptionId || !$paymentIntentId) { return; }
        $sub = $this->em->getRepository(Subscription::class)->findOneBy(['stripeSubscriptionId' => $subscriptionId]);
        if (!$sub) { return; }
        if ($this->em->getRepository(Payment::class)->findOneBy(['stripePaymentIntentId' => $paymentIntentId])) { return; }
        if ($type === 'invoice.payment_succeeded') {
            $amountPaid = isset($object['amount_paid']) ? ((int)$object['amount_paid']) / 100 : 0.0;
            $currency = strtoupper($object['currency'] ?? 'EUR');
            $p = (new Payment())
                ->setSubscription($sub)
                ->setAmount($amountPaid)
                ->setCurrency($currency)
                ->setStatus(PaymentStatus::SUCCEEDED)
                ->setStripePaymentIntentId($paymentIntentId);
            $this->em->persist($p);
        } elseif ($type === 'invoice.payment_failed') {
            $p = (new Payment())
                ->setSubscription($sub)
                ->setAmount(0.0)
                ->setCurrency(strtoupper($object['currency'] ?? 'EUR'))
                ->setStatus(PaymentStatus::FAILED)
                ->setStripePaymentIntentId($paymentIntentId);
            $this->em->persist($p);
        }
    }
}
