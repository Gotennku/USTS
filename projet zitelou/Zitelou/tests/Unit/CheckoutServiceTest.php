<?php

namespace App\Tests\Unit;

use App\StripeIntegration\Checkout\CheckoutService;
use App\StripeIntegration\Checkout\CheckoutSessionInput;
use App\StripeIntegration\Port\CustomerProviderInterface;
use App\StripeIntegration\Port\PlanPriceProviderInterface;
use App\Stripe\StripeClientFactory;
use Stripe\StripeClient;
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
        $fakeStripe = $this->getMockBuilder(StripeClient::class)->disableOriginalConstructor()->getMock();
        // Injection manuelle de la hiérarchie checkout->sessions->create()
        $fakeStripe->checkout = new class {
            public object $sessions;
            public function __construct() {
                $this->sessions = new class {
                    public function create(array $params) {
                        return (object)['url' => 'https://stripe/session/test'];
                    }
                };
            }
        };
        $clientFactory->method('create')->willReturn($fakeStripe);

        $service = new CheckoutService($clientFactory, $customerPort, $planPort);
        $result = $service->createSubscriptionCheckout(new CheckoutSessionInput('u1','p1','https://s','https://c'));
        self::assertSame('https://stripe/session/test', $result->url);
    }
}
