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

/**
 * @group legacy
 * Test legacy remplacé par la nouvelle architecture de webhooks.
 */
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
        $this->markTestSkipped('Legacy webhook handler supprimé. Test conservé uniquement pour référence historique.');
    }
}
