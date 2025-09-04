<?php

namespace App\StripeIntegration\Adapter;

use App\Entity\User;
use App\Stripe\StripeClientFactory;
use App\StripeIntegration\Port\CustomerProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class DoctrineCustomerProvider implements CustomerProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StripeClientFactory $clientFactory,
    ) {}

    public function ensureCustomer(string $userId, string $email): string
    {
        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->find($userId);
        if (!$user) {
            throw new RuntimeException('User introuvable');
        }
        if ($user->getStripeCustomerId()) {
            return $user->getStripeCustomerId();
        }
        $client = $this->clientFactory->create();
        $customer = $client->customers->create([
            'email' => $email ?: $user->getEmail(),
            'metadata' => ['user_id' => (string)$user->getId()],
        ]);
        $user->setStripeCustomerId($customer->id);
        $this->em->flush();
        return $customer->id;
    }
}
