<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\StripeIntegration\Checkout\CheckoutServiceInterface;
use App\StripeIntegration\Checkout\CheckoutSessionInput;
use App\StripeIntegration\Checkout\CheckoutSessionResult;

class StripeCheckoutAdditionalErrorsTest extends DatabaseWebTestCase
{
    public function testPlanNotFoundReturns404(): void
    {
        $user = (new User())->setEmail('nofound@example.test')->setPassword('x');
        $this->em->persist($user); $this->em->flush();
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $this->client->request('POST', '/api/stripe/checkout/session/999999', [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([]));
        $resp = $this->client->getResponse();
        self::assertSame(404, $resp->getStatusCode(), $resp->getContent());
    }

    public function testCheckoutServiceThrowsReturns400(): void
    {
        $user = (new User())->setEmail('throw@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())->setName('Throw')->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR')->setStripePriceId('price_throw');
        $this->em->persist($user); $this->em->persist($plan); $this->em->flush();
        $stub = new class implements CheckoutServiceInterface {
            public function createSubscriptionCheckout(CheckoutSessionInput $input): CheckoutSessionResult
            { throw new \RuntimeException('boom'); }
        };
        static::getContainer()->set(CheckoutServiceInterface::class, $stub);
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $this->client->request('POST', '/api/stripe/checkout/session/'.$plan->getId(), [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([]));
        $resp = $this->client->getResponse();
        self::assertSame(400, $resp->getStatusCode(), $resp->getContent());
        $data = json_decode($resp->getContent(), true);
        self::assertArrayHasKey('error', $data);
        self::assertStringContainsString('boom', $data['error']);
    }
}
