<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuthToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'authTokens', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $token;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

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
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function getToken(): string
    {
        return $this->token;
    }
    public function setToken(string $t): self
    {
        $this->token = $t;
        return $this;
    }
    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function setExpiresAt(DateTimeImmutable $e): self
    {
        $this->expiresAt = $e;
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
