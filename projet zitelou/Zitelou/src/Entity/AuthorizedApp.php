<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuthorizedApp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'authorizedApps', targetEntity: Child::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Child $child = null;

    #[ORM\Column(length: 150)]
    private string $appName;

    #[ORM\Column(length: 200)]
    private string $packageName;

    #[ORM\Column(type: 'boolean')]
    private bool $isAllowed = true;

    public function getId(): ?int { return $this->id; }
    public function getChild(): ?Child { return $this->child; }
    public function setChild(?Child $child): self { $this->child = $child; return $this; }
    public function getAppName(): string { return $this->appName; }
    public function setAppName(string $n): self { $this->appName = $n; return $this; }
    public function getPackageName(): string { return $this->packageName; }
    public function setPackageName(string $p): self { $this->packageName = $p; return $this; }
    public function isAllowed(): bool { return $this->isAllowed; }
    public function setIsAllowed(bool $a): self { $this->isAllowed = $a; return $this; }
}
