<?php

namespace App\Controller\Api;

use App\Entity\StripeWebhookLog;
use App\Service\Stripe\StripeWebhookHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/api/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
class StripeWebhookController
{
    public function __construct(
        private readonly StripeWebhookHandler $handler,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sig = $request->headers->get('stripe-signature');

        try {
            $event = $this->handler->constructEvent($payload, $sig ?? '');
        } catch (Throwable $e) {
            return new Response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        // Log brut
        $log = (new StripeWebhookLog())
            ->setEventType($event->type)
            ->setPayload(json_decode($payload, true) ?? []);
        $this->em->persist($log);

        $this->handler->handle($event, $log);
        $this->em->flush();

        return new Response('ok');
    }
}
