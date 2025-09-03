<?php

namespace App\Tests\Stripe;

use App\Entity\StripeWebhookLog;
use App\Entity\SubscriptionPlan;
use App\Entity\User;
use App\Enum\SubscriptionStatus;
use App\Service\Stripe\StripeWebhookHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Stripe\Event;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StripeWebhookFunctionalTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface
    {
        return new \App\Kernel('test', true);
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        // Reconstruction complète du schéma pour garantir la présence de toutes les colonnes
        $tool = new SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropDatabase();
        if ($classes) {
            $tool->createSchema($classes);
        }
    }

    public function testHandleSubscriptionCreated(): void
    {
        $user = (new User())->setEmail('test@example.org')->setPassword('x');
        $plan = (new SubscriptionPlan())->setName('Plan')->setDurationDays(30)->setPrice('9.99')->setCurrency('EUR');
        $this->em->persist($user);
        $this->em->persist($plan);
        $this->em->flush();

        /** @var StripeWebhookHandler $handler */
        $handler = self::getContainer()->get(StripeWebhookHandler::class);

        $event = Event::constructFrom([
            'id' => 'evt_local_1',
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_local_123',
                    'metadata' => [ 'user_id' => (string)$user->getId(), 'plan_id' => (string)$plan->getId() ],
                ],
            ],
        ]);

        $log = (new StripeWebhookLog())
            ->setEventType($event->type)
            ->setPayload($event->toArray());
        $handler->handle($event, $log);
        $this->em->persist($log);
        $this->em->flush();

        $subs = $this->em->getRepository(\App\Entity\Subscription::class)->findAll();
        $this->assertCount(1, $subs, 'Subscription should be created');
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subs[0]->getStatus());
        $this->assertTrue($log->isProcessed());
    }
}
