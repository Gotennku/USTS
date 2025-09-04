<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stripe;

use App\Stripe\StripeClientFactory;
use PHPUnit\Framework\TestCase;
use Stripe\StripeClient;

class StripeClientFactoryTest extends TestCase
{
    public function testCreateReturnsStripeClient(): void
    {
        $factory = new StripeClientFactory('sk_test_xxx', 'whsec_123');
        $client = $factory->create();
        $this->assertInstanceOf(StripeClient::class, $client);
        $this->assertSame('whsec_123', $factory->getWebhookSecret());
    }
}
