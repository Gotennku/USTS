<?php

namespace App\StripeIntegration\Checkout;

use App\Stripe\StripeClientFactory;
use App\StripeIntegration\Port\CustomerProviderInterface;
use App\StripeIntegration\Exception\EmptyReturnUrlException;

class BillingPortalService implements BillingPortalServiceInterface
{
    public function __construct(
        private readonly StripeClientFactory $clientFactory,
        private readonly CustomerProviderInterface $customers,
    ) {}

    public function createPortalUrl(string $userId, string $returnUrl): BillingPortalUrlResult
    {
        if ($returnUrl === '') {
            throw new EmptyReturnUrlException('Return URL vide');
        }
        $client = $this->clientFactory->create();
        $customerId = $this->customers->ensureCustomer($userId, '');
        $session = $client->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);
        return new BillingPortalUrlResult($session->url);
    }
}
