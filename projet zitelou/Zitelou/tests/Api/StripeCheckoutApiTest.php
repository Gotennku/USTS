<?php

namespace App\Tests\Api;

use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Tests\Api\DatabaseWebTestCase;
use App\StripeIntegration\Checkout\CheckoutServiceInterface;
use App\StripeIntegration\Checkout\CheckoutSessionInput;
use App\StripeIntegration\Checkout\CheckoutSessionResult;
use Symfony\Component\HttpFoundation\Response;

class StripeCheckoutApiTest extends DatabaseWebTestCase
{

    // setUp hérité prépare schéma une seule fois

    private function createUser(string $email = 'u@example.org'): User
    {
        $u = (new User())->setEmail($email)->setPassword('x');
        $this->em->persist($u);
        return $u;
    }

    private function createPlan(): SubscriptionPlan
    {
        $p = (new SubscriptionPlan())
            ->setName('Plan Test')
            ->setDurationDays(30)
            ->setPrice('9.99')
            ->setCurrency('EUR')
            ->setStripePriceId('price_dummy');
        $this->em->persist($p);
        return $p;
    }

    public function testCheckoutSessionRequiresAuth(): void
    {
        $plan = $this->createPlan();
        $this->em->flush();
        $this->client->request('POST', '/api/stripe/checkout/session/'.$plan->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $response = $this->client->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(401, $response->getStatusCode());
    }

    public function testCheckoutSessionOk(): void
    {
        $user = $this->createUser();
        $plan = $this->createPlan();
        $this->em->flush();
        // Stub du nouveau service CheckoutServiceInterface
        $stub = new class() implements CheckoutServiceInterface {
            public function createSubscriptionCheckout(CheckoutSessionInput $input): CheckoutSessionResult
            {
                return new CheckoutSessionResult('https://stripe.test/checkout/session/fake123');
            }
        };
        static::getContainer()->set(CheckoutServiceInterface::class, $stub);
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $this->client->request('POST', '/api/stripe/checkout/session/'.$plan->getId(), [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'success_url' => 'https://example.test/success',
            'cancel_url' => 'https://example.test/cancel'
        ]));
        $response = $this->client->getResponse();
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(201, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('checkout_url', $data);
        self::assertNotEmpty($data['checkout_url']);
    }

    public function testBillingPortalNotImplementedYet(): void
    {
        $user = $this->createUser('portal@example.org');
        $this->em->flush();
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $this->client->request('POST', '/api/stripe/portal', [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['return_url' => 'https://example.test/account']));
        $response = $this->client->getResponse();
        self::assertEquals(501, $response->getStatusCode(), $response->getContent());
    }
}
