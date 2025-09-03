<?php

namespace App\Entity;

use App\Enum\PaymentStatus;
use App\Repository\PaymentRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Subscription::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Subscription $subscription = null;

    #[ORM\Column(type: 'float')]
    private float $amount;

    #[ORM\Column(length: 10)]
    private string $currency;

    #[ORM\Column(enumType: PaymentStatus::class)]
    private PaymentStatus $status; // enum

    #[ORM\Column(length: 255)]
    private string $stripePaymentIntentId;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }
    public function setSubscription(?Subscription $s): self
    {
        $this->subscription = $s;
        return $this;
    }
    public function getAmount(): float
    {
        return $this->amount;
    }
    public function setAmount(float $a): self
    {
        $this->amount = $a;
        return $this;
    }
    public function getCurrency(): string
    {
        return $this->currency;
    }
    public function setCurrency(string $c): self
    {
        $this->currency = $c;
        return $this;
    }
    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }
    public function setStatus(PaymentStatus $s): self
    {
        $this->status = $s;
        return $this;
    }
    public function getStripePaymentIntentId(): string
    {
        return $this->stripePaymentIntentId;
    }
    public function setStripePaymentIntentId(string $id): self
    {
        $this->stripePaymentIntentId = $id;
        return $this;
    }
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(DateTimeImmutable $c): self
    {
        $this->createdAt = $c;
        return $this;
    }

}
