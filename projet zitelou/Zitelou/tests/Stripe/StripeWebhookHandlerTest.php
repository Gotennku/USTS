<?php

namespace App\Tests\Stripe;

use PHPUnit\Framework\TestCase;
use App\Service\Stripe\StripeWebhookHandler;
use App\Stripe\StripeClientFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\SubscriptionPlan;
use App\Enum\SubscriptionStatus;
use Stripe\Event;

class StripeWebhookHandlerTest extends TestCase
{
    public function testSubscriptionCreatedCreatesEntity(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $factory = $this->createMock(StripeClientFactory::class);
        $handler = new StripeWebhookHandler($factory, $em);

        $user = (new User())->setEmail('a@b.c');
        $refUser = new \ReflectionProperty(User::class, 'id');
        $refUser->setAccessible(true); $refUser->setValue($user, 10);
        $plan = (new SubscriptionPlan())->setName('Plan')->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR');
        $refPlan = new \ReflectionProperty(SubscriptionPlan::class, 'id');
        $refPlan->setAccessible(true); $refPlan->setValue($plan, 5);

        $em->method('getRepository')->willReturnCallback(function(string $class) use ($user, $plan) {
            return new class($class, $user, $plan) implements \\Doctrine\\Persistence\\ObjectRepository {
                public function __construct(private string $class, private $user, private $plan) {}
                public function find(mixed $id): object|null { return match($this->class) { User::class => $this->user, SubscriptionPlan::class => $this->plan, default => null }; }
                public function findAll(): array { return []; }
                public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array { return []; }
                public function findOneBy(array $criteria): object|null { return null; }
                public function getClassName(): string { return $this->class; }
            };
        });

        $em->expects($this->atLeastOnce())->method('persist');

        $event = Event::constructFrom([
            'id' => 'evt_1',
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_123',
                    'metadata' => [ 'user_id' => '10', 'plan_id' => '5' ],
                ]
            ]
        ]);

        $log = new \App\Entity\StripeWebhookLog();
        $handler->handle($event, $log);
        $this->assertTrue($log->isProcessed());
    }
}
