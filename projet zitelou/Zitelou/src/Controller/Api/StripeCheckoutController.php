<?php

namespace App\Controller\Api;

use App\Entity\SubscriptionPlan;
use App\StripeIntegration\Checkout\CheckoutServiceInterface;
use App\StripeIntegration\Checkout\CheckoutSessionInput;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/stripe', name: 'stripe_')]

class StripeCheckoutController extends AbstractController
{
    public function __construct(
        private readonly CheckoutServiceInterface $checkout,
        private readonly EntityManagerInterface $em,
        private readonly TokenStorageInterface $tokens,
    ) {
    }

    #[Route('/checkout/session/{id}', name: 'checkout_session', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createSession(int $id, Request $request): JsonResponse
    {
        $plan = $this->em->getRepository(SubscriptionPlan::class)->find($id);
        if (!$plan) {
            return new JsonResponse(['error' => 'Plan introuvable'], 404);
        }
        $token = $this->tokens->getToken();
        if (!$token || !is_object($token->getUser())) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }
        /** @var \App\Entity\User $user */
        $user = $token->getUser();
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];
        $success = $payload['success_url'] ?? 'https://example.com/success';
        $cancel = $payload['cancel_url'] ?? 'https://example.com/cancel';
        try {
            $result = $this->checkout->createSubscriptionCheckout(new CheckoutSessionInput(
                (string)$user->getId(),
                (string)$plan->getId(),
                $success,
                $cancel
            ));
            return new JsonResponse(['checkout_url' => $result->url], 201);
        } catch (Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/portal', name: 'billing_portal', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function portal(Request $request): JsonResponse
    {
        $token = $this->tokens->getToken();
        if (!$token || !is_object($token->getUser())) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
        }
        /** @var \App\Entity\User $user */
        $user = $token->getUser();
        $payload = json_decode($request->getContent() ?: '[]', true) ?? [];
        $returnUrl = $payload['return_url'] ?? 'https://example.com/account';
    // TODO: implémenter via nouveau service (BillingPortalService) lors de l'extraction complète.
    return new JsonResponse(['error' => 'Portal non encore migré'], 501);
    }
}
