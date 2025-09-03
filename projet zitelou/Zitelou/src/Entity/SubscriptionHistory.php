<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\SubscriptionStatus;
use App\Enum\SubscriptionEvent;

#[ORM\Entity]
class SubscriptionHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historyEntries', targetEntity: Subscription::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Subscription $subscription = null;

    #[ORM\Column(enumType: SubscriptionStatus::class)]
    private SubscriptionStatus $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $changedAt;

    #[ORM\Column(enumType: SubscriptionEvent::class)]
    private SubscriptionEvent $event; // enum

    public function __construct()
    { $this->changedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getSubscription(): ?Subscription { return $this->subscription; }
    public function setSubscription(?Subscription $s): self { $this->subscription = $s; return $this; }
    public function getStatus(): SubscriptionStatus { return $this->status; }
    public function setStatus(SubscriptionStatus $s): self { $this->status = $s; return $this; }
    public function getChangedAt(): \DateTimeImmutable { return $this->changedAt; }
    public function setChangedAt(\DateTimeImmutable $c): self { $this->changedAt = $c; return $this; }
    public function getEvent(): SubscriptionEvent { return $this->event; }
    public function setEvent(SubscriptionEvent $e): self { $this->event = $e; return $this; }
}
