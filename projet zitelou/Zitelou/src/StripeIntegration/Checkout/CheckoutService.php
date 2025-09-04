<?php

namespace App\StripeIntegration\Checkout;

use App\Stripe\StripeClientFactory;
use App\StripeIntegration\Port\CustomerProviderInterface;
use App\StripeIntegration\Port\PlanPriceProviderInterface;
use RuntimeException;

class CheckoutService implements CheckoutServiceInterface
{
    public function __construct(
        private readonly StripeClientFactory $clientFactory,
        private readonly CustomerProviderInterface $customers,
        private readonly PlanPriceProviderInterface $plans,
    ) {}

    public function createSubscriptionCheckout(CheckoutSessionInput $input): CheckoutSessionResult
    {
        $priceId = $this->plans->getPriceId($input->planId);
        if (!$priceId) {
            throw new RuntimeException('Plan non lié à un price Stripe');
        }
        $client = $this->clientFactory->create();
        $customerId = $this->customers->ensureCustomer($input->userId, ''); // email géré côté adapter
        $session = $client->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[ 'price' => $priceId, 'quantity' => 1 ]],
            'success_url' => $input->successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $input->cancelUrl,
            'metadata' => [
                'user_id' => $input->userId,
                'plan_id' => $input->planId,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $input->userId,
                    'plan_id' => $input->planId,
                ],
            ],
        ]);
        return new CheckoutSessionResult($session->url);
    }
}
