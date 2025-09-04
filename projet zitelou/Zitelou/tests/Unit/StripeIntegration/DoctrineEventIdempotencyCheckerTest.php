<?php

declare(strict_types=1);

namespace App\Tests\Unit\StripeIntegration;

use App\Entity\StripeWebhookLog;
use App\StripeIntegration\Webhook\Idempotency\DoctrineEventIdempotencyChecker;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineEventIdempotencyCheckerTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface { return new \App\Kernel('test', true); }

    private function resetSchema(): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($em);
        $classes = $em->getMetadataFactory()->getAllMetadata();
        $tool->dropDatabase();
        $tool->createSchema($classes);
    }

    public function testIsAlreadyProcessedFalseThenTrue(): void
    {
        self::bootKernel();
        $this->resetSchema();
        $em = self::getContainer()->get('doctrine')->getManager();
        $checker = new DoctrineEventIdempotencyChecker($em);

        $eventId = 'evt_test_123';
        self::assertFalse($checker->isAlreadyProcessed($eventId));

        $checker->markProcessed($eventId, 'invoice.payment_succeeded', ['id' => 'in_1']);
        self::assertTrue($checker->isAlreadyProcessed($eventId));
    }

    public function testMarkProcessedIdempotentAndEnrichesPayloadIfEmpty(): void
    {
        self::bootKernel();
        $this->resetSchema();
        $em = self::getContainer()->get('doctrine')->getManager();
        $checker = new DoctrineEventIdempotencyChecker($em);

        $eventId = 'evt_mark_1';
        $checker->markProcessed($eventId, 'customer.subscription.created', ['id' => 'sub_1']);
        $logRepo = $em->getRepository(StripeWebhookLog::class);
        $log = $logRepo->findOneBy(['eventId' => $eventId]);
        self::assertNotNull($log);
        self::assertTrue($log->isProcessed());
        self::assertSame('customer.subscription.created', $log->getEventType());
        self::assertSame(['object' => ['id' => 'sub_1']], $log->getPayload());

        // Simuler un log existant sans payload pour tester enrichissement
        $log->setPayload([]); $log->setProcessed(false); $em->flush();
        $checker->markProcessed($eventId, 'customer.subscription.created', ['id' => 'sub_1']);
        $em->refresh($log);
        self::assertSame(['object' => ['id' => 'sub_1']], $log->getPayload());
        self::assertTrue($log->isProcessed());
    }
}
