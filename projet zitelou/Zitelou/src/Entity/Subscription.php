<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Enum\SubscriptionStatus;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ApiResource(operations: [new Get(), new GetCollection(), new Post(), new Patch(), new Delete()], normalizationContext: ['groups' => ['subscription:read']], denormalizationContext: ['groups' => ['subscription:write']])]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'partial', 'user.email' => 'ipartial'])]
#[ApiFilter(OrderFilter::class, properties: ['id','startDate','endDate'], arguments: ['orderParameterName' => 'order'])]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['subscription:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'subscription', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['subscription:read','subscription:write'])]
    private ?User $user = null;

    #[ORM\Column(enumType: SubscriptionStatus::class)]
    #[Groups(['subscription:read','subscription:write'])]
    private SubscriptionStatus $status;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['subscription:read','subscription:write'])]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['subscription:read','subscription:write'])]
    private ?\DateTime $endDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['subscription:read','subscription:write'])]
    private ?string $stripeSubscriptionId = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions', targetEntity: SubscriptionPlan::class)]
    private ?SubscriptionPlan $plan = null;

    /** @var Collection<int, Payment> */
    #[ORM\OneToMany(mappedBy: 'subscription', targetEntity: Payment::class, orphanRemoval: false)]
    private Collection $payments;

    /** @var Collection<int, SubscriptionHistory> */
    #[ORM\OneToMany(mappedBy: 'subscription', targetEntity: SubscriptionHistory::class, orphanRemoval: true)]
    private Collection $historyEntries;

    /** @var Collection<int, StripeWebhookLog> */
    #[ORM\OneToMany(mappedBy: 'subscription', targetEntity: StripeWebhookLog::class)]
    private Collection $webhookLogs;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->historyEntries = new ArrayCollection();
        $this->webhookLogs = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStatus(): SubscriptionStatus { return $this->status; }
    public function setStatus(SubscriptionStatus $status): self { $this->status = $status; return $this; }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(?string $stripeSubscriptionId): self
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
        return $this;
    }

    public function getPlan(): ?SubscriptionPlan { return $this->plan; }
    public function setPlan(?SubscriptionPlan $p): self { $this->plan = $p; return $this; }

    /** @return Collection<int, Payment> */
    public function getPayments(): Collection { return $this->payments; }
    public function addPayment(Payment $payment): self
    { if(!$this->payments->contains($payment)){ $this->payments->add($payment); $payment->setSubscription($this);} return $this; }
    public function removePayment(Payment $payment): self
    { if($this->payments->removeElement($payment) && $payment->getSubscription()===$this){ $payment->setSubscription(null);} return $this; }

    /** @return Collection<int, SubscriptionHistory> */
    public function getHistoryEntries(): Collection { return $this->historyEntries; }
    public function addHistoryEntry(SubscriptionHistory $h): self
    { if(!$this->historyEntries->contains($h)){ $this->historyEntries->add($h); $h->setSubscription($this);} return $this; }
    public function removeHistoryEntry(SubscriptionHistory $h): self
    { if($this->historyEntries->removeElement($h) && $h->getSubscription()===$this){ $h->setSubscription(null);} return $this; }

    /** @return Collection<int, StripeWebhookLog> */
    public function getWebhookLogs(): Collection { return $this->webhookLogs; }
    public function addWebhookLog(StripeWebhookLog $l): self
    { if(!$this->webhookLogs->contains($l)){ $this->webhookLogs->add($l); $l->setSubscription($this);} return $this; }
    public function removeWebhookLog(StripeWebhookLog $l): self
    { if($this->webhookLogs->removeElement($l) && $l->getSubscription()===$this){ $l->setSubscription(null);} return $this; }

}
