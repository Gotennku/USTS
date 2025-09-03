<?php

namespace App\Service\Stripe;

use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Stripe\StripeClientFactory;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class StripeCheckoutService
{
    public function __construct(
        private readonly StripeClientFactory $factory,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function ensureCustomer(User $user): string
    {
        $client = $this->factory->create();
        if ($user->getStripeCustomerId()) {
            return $user->getStripeCustomerId();
        }
        $customer = $client->customers->create([
            'email' => $user->getEmail(),
            'metadata' => ['user_id' => $user->getId()],
        ]);
        $user->setStripeCustomerId($customer->id);
        $this->em->flush();
        return $customer->id;
    }

    public function createSubscriptionSession(User $user, SubscriptionPlan $plan, string $successUrl, string $cancelUrl): string
    {
        $client = $this->factory->create();
        $customerId = $this->ensureCustomer($user);
        if (!$plan->getStripePriceId()) {
            throw new RuntimeException('Plan non lié à un price Stripe');
        }
        // Important: subscription_data -> metadata pour retrouver user & plan dans l'event customer.subscription.created
        $session = $client->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[ 'price' => $plan->getStripePriceId(), 'quantity' => 1 ]],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [ // metadata sur la session (utile pour debug)
                'user_id' => (string)$user->getId(),
                'plan_id' => (string)$plan->getId(),
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => (string)$user->getId(),
                    'plan_id' => (string)$plan->getId(),
                ],
            ],
        ]);
        return $session->url;
    }

    public function createBillingPortalUrl(User $user, string $returnUrl): string
    {
        $client = $this->factory->create();
        $customerId = $this->ensureCustomer($user);
        $session = $client->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);
        return $session->url;
    }
}
