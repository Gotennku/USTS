<?php

namespace App\Tests\Api;

use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Tests\Api\DatabaseWebTestCase;
use App\StripeIntegration\Checkout\CheckoutServiceInterface;
use App\StripeIntegration\Checkout\CheckoutSessionInput;
use App\StripeIntegration\Checkout\CheckoutSessionResult;
use App\StripeIntegration\Checkout\BillingPortalServiceInterface;
use App\StripeIntegration\Checkout\BillingPortalUrlResult;
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

    public function testCheckoutSessionFailsWithoutPrice(): void
    {
        $user = $this->createUser('noprice@example.org');
        $plan = (new SubscriptionPlan())
            ->setName('NoPrice')
            ->setDurationDays(30)
            ->setPrice('4.99')
            ->setCurrency('EUR'); // pas de stripePriceId
        $this->em->persist($plan);
        $this->em->flush();
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $this->client->request('POST', '/api/stripe/checkout/session/'.$plan->getId(), [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([]));
        $response = $this->client->getResponse();
        self::assertEquals(400, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('error', $data);
    }

    public function testBillingPortalEndpoint(): void
    {
        $user = $this->createUser('portal@example.org');
        $this->em->flush();
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        // Stub BillingPortalService pour éviter appel externe Stripe
        $portalStub = new class() implements BillingPortalServiceInterface {
            public function createPortalUrl(string $userId, string $returnUrl): BillingPortalUrlResult
            {
                return new BillingPortalUrlResult('https://stripe.test/portal/session/abc123');
            }
        };
        static::getContainer()->set(BillingPortalServiceInterface::class, $portalStub);
        $this->client->request('POST', '/api/stripe/portal', [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['return_url' => 'https://example.test/account']));
        $response = $this->client->getResponse();
        self::assertEquals(201, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('portal_url', $data);
        self::assertNotEmpty($data['portal_url']);
    }

    public function testBillingPortalRequiresAuth(): void
    {
        $this->client->request('POST', '/api/stripe/portal', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([]));
        $resp = $this->client->getResponse();
        self::assertEquals(401, $resp->getStatusCode(), $resp->getContent());
    }

    public function testBillingPortalEndpointDefaultReturnUrl(): void
    {
        $user = $this->createUser('portaldefault@example.org');
        $this->em->flush();
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $portalStub = new class() implements BillingPortalServiceInterface {
            public function createPortalUrl(string $userId, string $returnUrl): BillingPortalUrlResult
            {
                // On vérifie implicitement que le défaut est passé (mais sans assertion sur returnUrl car pas exposé dans réponse)
                return new BillingPortalUrlResult('https://stripe.test/portal/session/default123');
            }
        };
        static::getContainer()->set(BillingPortalServiceInterface::class, $portalStub);
        $this->client->request('POST', '/api/stripe/portal', [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([])); // pas de return_url => valeur par défaut utilisée
        $response = $this->client->getResponse();
        self::assertEquals(201, $response->getStatusCode(), $response->getContent());
        $data = json_decode($response->getContent(), true);
        self::assertArrayHasKey('portal_url', $data);
    }

    public function testBillingPortalServiceThrowsReturns400(): void
    {
        $user = $this->createUser('portalerror@example.org');
        $this->em->flush();
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $throwingStub = new class() implements BillingPortalServiceInterface {
            public function createPortalUrl(string $userId, string $returnUrl): BillingPortalUrlResult
            { throw new \RuntimeException('portal_fail'); }
        };
        static::getContainer()->set(BillingPortalServiceInterface::class, $throwingStub);
        $this->client->request('POST', '/api/stripe/portal', [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode(['return_url' => 'https://example.test/account']));
        $resp = $this->client->getResponse();
        self::assertEquals(400, $resp->getStatusCode(), $resp->getContent());
        $data = json_decode($resp->getContent(), true);
        self::assertArrayHasKey('error', $data);
        self::assertStringContainsString('portal_fail', $data['error']);
    }
}
