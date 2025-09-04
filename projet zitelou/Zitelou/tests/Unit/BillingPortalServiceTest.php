<?php

namespace App\Tests\Unit;

use App\StripeIntegration\Checkout\BillingPortalService;
use App\StripeIntegration\Exception\EmptyReturnUrlException;
use App\StripeIntegration\Port\CustomerProviderInterface;
use App\Stripe\StripeClientFactory;
use PHPUnit\Framework\TestCase;
use App\Tests\Stub\Stripe\StripeStubClient;

class BillingPortalServiceTest extends TestCase
{
    public function testCreatePortalUrl(): void
    {
        $factory = $this->createMock(StripeClientFactory::class);
        $customers = $this->createMock(CustomerProviderInterface::class);
    $customers->method('ensureCustomer')->willReturn('cus_123');
    $fakeClient = new StripeStubClient();
        $factory->method('create')->willReturn($fakeClient);
        $service = new BillingPortalService($factory, $customers);
        $result = $service->createPortalUrl('user123', 'https://app.test/account');
        self::assertSame('https://stripe/portal/session/test', $result->url);
    }

    public function testCreatePortalUrlFailsOnEmptyReturnUrl(): void
    {
        $factory = $this->createMock(StripeClientFactory::class);
        $customers = $this->createMock(CustomerProviderInterface::class);
        $service = new BillingPortalService($factory, $customers);
        $this->expectException(EmptyReturnUrlException::class);
        $service->createPortalUrl('user123', '');
    }
}
