<?php
namespace App\StripeIntegration\Webhook\Idempotency;

use App\Entity\StripeWebhookLog;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineEventIdempotencyChecker implements EventIdempotencyCheckerInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function isAlreadyProcessed(string $eventId): bool
    {
        return (bool)$this->em->getRepository(StripeWebhookLog::class)->findOneBy([
            'eventId' => $eventId
        ]);
    }

    public function markProcessed(string $eventId, string $type, array $objectPayload): void
    {
        $repo = $this->em->getRepository(StripeWebhookLog::class);
        $log = $repo->findOneBy(['eventId' => $eventId]);
        if (!$log) {
            $log = new StripeWebhookLog();
            $log->setEventId($eventId)->setEventType($type)->setPayload(['object' => $objectPayload]);
            $this->em->persist($log);
        } else {
            // enrichir payload si vide
            if (empty($log->getPayload())) {
                $log->setPayload(['object' => $objectPayload]);
            }
        }
        $log->setProcessed(true);
        $this->em->flush();
    }
}
