<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userLogs', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 45)]
    private string $ipAddress;

    #[ORM\Column(length: 100)]
    private string $device;

    #[ORM\Column(length: 100)]
    private string $action;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $timestamp;

    public function __construct()
    { $this->timestamp = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getIpAddress(): string { return $this->ipAddress; }
    public function setIpAddress(string $v): self { $this->ipAddress = $v; return $this; }
    public function getDevice(): string { return $this->device; }
    public function setDevice(string $d): self { $this->device = $d; return $this; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $a): self { $this->action = $a; return $this; }
    public function getTimestamp(): \DateTimeImmutable { return $this->timestamp; }
    public function setTimestamp(\DateTimeImmutable $t): self { $this->timestamp = $t; return $this; }
}
