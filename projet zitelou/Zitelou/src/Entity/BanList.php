<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BanList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'banLists', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private string $reason;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $bannedUntil = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function getReason(): string
    {
        return $this->reason;
    }
    public function setReason(string $r): self
    {
        $this->reason = $r;
        return $this;
    }
    public function getBannedUntil(): ?DateTimeImmutable
    {
        return $this->bannedUntil;
    }
    public function setBannedUntil(?DateTimeImmutable $d): self
    {
        $this->bannedUntil = $d;
        return $this;
    }
}
