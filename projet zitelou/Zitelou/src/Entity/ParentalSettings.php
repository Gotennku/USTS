<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ParentalSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'parentalSettings', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 10)]
    private string $pinCode;

    #[ORM\Column(type: 'boolean')]
    private bool $safeMode = true;

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
    public function getPinCode(): string
    {
        return $this->pinCode;
    }
    public function setPinCode(string $p): self
    {
        $this->pinCode = $p;
        return $this;
    }
    public function isSafeMode(): bool
    {
        return $this->safeMode;
    }
    public function setSafeMode(bool $s): self
    {
        $this->safeMode = $s;
        return $this;
    }
}
