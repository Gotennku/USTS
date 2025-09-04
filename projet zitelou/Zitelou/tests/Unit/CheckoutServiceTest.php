<?php

namespace App\Tests\Unit;

use App\StripeIntegration\Checkout\CheckoutService;
use App\StripeIntegration\Checkout\CheckoutSessionInput;
use App\StripeIntegration\Port\CustomerProviderInterface;
use App\StripeIntegration\Port\PlanPriceProviderInterface;
use App\Stripe\StripeClientFactory;
use App\StripeIntegration\Exception\PriceNotConfiguredException;
use App\Tests\Stub\Stripe\StripeStubClient;
use PHPUnit\Framework\TestCase;

class CheckoutServiceTest extends TestCase
{
    public function testCreateSubscriptionCheckout(): void
    {
        $clientFactory = $this->createMock(StripeClientFactory::class);
        $customerPort = $this->createMock(CustomerProviderInterface::class);
        $planPort = $this->createMock(PlanPriceProviderInterface::class);

        $customerPort->method('ensureCustomer')->willReturn('cus_123');
        $planPort->method('getPriceId')->willReturn('price_456');

        // On renvoie un objet qui est bien un StripeClient (mock) pour respecter le type de retour strict de StripeClientFactory::create()
    $fakeStripe = new StripeStubClient();
        $clientFactory->method('create')->willReturn($fakeStripe);

        $service = new CheckoutService($clientFactory, $customerPort, $planPort);
        $result = $service->createSubscriptionCheckout(new CheckoutSessionInput('u1','p1','https://s','https://c'));
        self::assertSame('https://stripe/session/test', $result->url);
    }

    public function testCreateSubscriptionCheckoutFailsWhenPriceMissing(): void
    {
        $clientFactory = $this->createMock(StripeClientFactory::class);
        $customerPort = $this->createMock(CustomerProviderInterface::class);
        $planPort = $this->createMock(PlanPriceProviderInterface::class);

        $planPort->method('getPriceId')->willReturn(null); // Simule plan sans price
        $this->expectException(PriceNotConfiguredException::class);

        $service = new CheckoutService($clientFactory, $customerPort, $planPort);
        $service->createSubscriptionCheckout(new CheckoutSessionInput('u1','plan-missing','https://s','https://c'));
    }
}
