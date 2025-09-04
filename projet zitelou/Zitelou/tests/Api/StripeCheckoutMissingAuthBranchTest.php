<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\SubscriptionPlan;

class StripeCheckoutMissingAuthBranchTest extends DatabaseWebTestCase
{
    public function testCreateSessionBranchUserTokenNull(): void
    {
        $plan = (new SubscriptionPlan())
            ->setName('NoAuthPlan')
            ->setDurationDays(30)
            ->setPrice('5.00')
            ->setCurrency('EUR')
            ->setStripePriceId('price_noauth');
        $this->em->persist($plan); $this->em->flush();
        // Pas de JWT => token storage vide => 401 via branche utilisateur non authentifié
        $this->client->request('POST', '/api/stripe/checkout/session/'.$plan->getId(), [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([]));
        $resp = $this->client->getResponse();
        self::assertSame(401, $resp->getStatusCode(), $resp->getContent());
    }

    public function testCheckoutSessionOkWithDefaultUrls(): void
    {
        // Couverture du chemin succès utilisant les URLs par défaut (pas de success_url / cancel_url fournis)
        $user = (new \App\Entity\User())->setEmail('defaulturl@example.test')->setPassword('x');
        $plan = (new SubscriptionPlan())
            ->setName('DefaultUrlPlan')
            ->setDurationDays(30)
            ->setPrice('9.50')
            ->setCurrency('EUR')
            ->setStripePriceId('price_default');
        $this->em->persist($user); $this->em->persist($plan); $this->em->flush();
        // Stub checkout pour retourner une URL
        $stub = new class implements \App\StripeIntegration\Checkout\CheckoutServiceInterface {
            public function createSubscriptionCheckout(\App\StripeIntegration\Checkout\CheckoutSessionInput $input): \App\StripeIntegration\Checkout\CheckoutSessionResult
            { return new \App\StripeIntegration\Checkout\CheckoutSessionResult('https://stripe.test/checkout/session/defaultUrls'); }
        };
        static::getContainer()->set(\App\StripeIntegration\Checkout\CheckoutServiceInterface::class, $stub);
        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);
        $this->client->request('POST', '/api/stripe/checkout/session/'.$plan->getId(), [], [], [
            'HTTP_Authorization' => 'Bearer '.$token,
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([])); // pas de success_url ni cancel_url
        $resp = $this->client->getResponse();
        self::assertSame(201, $resp->getStatusCode(), $resp->getContent());
        $data = json_decode($resp->getContent(), true);
        self::assertArrayHasKey('checkout_url', $data);
        self::assertStringContainsString('defaultUrls', $data['checkout_url']);
    }
}
