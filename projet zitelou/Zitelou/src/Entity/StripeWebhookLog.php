<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class StripeWebhookLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'webhookLogs', targetEntity: Subscription::class)]
    private ?Subscription $subscription = null;

    #[ORM\Column(length: 100)]
    private string $eventType;

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $receivedAt;

    #[ORM\Column(type: 'boolean')]
    private bool $processed = false;

    public function __construct()
    { $this->receivedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getSubscription(): ?Subscription { return $this->subscription; }
    public function setSubscription(?Subscription $s): self { $this->subscription = $s; return $this; }
    public function getEventType(): string { return $this->eventType; }
    public function setEventType(string $e): self { $this->eventType = $e; return $this; }
    public function getPayload(): array { return $this->payload; }
    public function setPayload(array $p): self { $this->payload = $p; return $this; }
    public function getReceivedAt(): \DateTimeImmutable { return $this->receivedAt; }
    public function setReceivedAt(\DateTimeImmutable $d): self { $this->receivedAt = $d; return $this; }
    public function isProcessed(): bool { return $this->processed; }
    public function setProcessed(bool $p): self { $this->processed = $p; return $this; }
}
