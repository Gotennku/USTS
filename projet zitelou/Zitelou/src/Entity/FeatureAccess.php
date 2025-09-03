<?php

namespace App\Entity;

use App\Repository\FeatureAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeatureAccessRepository::class)]
class FeatureAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Child::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Child $child = null;

    #[ORM\Column(length: 100)]
    private string $feature;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $limits = [];

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getChild(): ?Child
    {
        return $this->child;
    }
    public function setChild(?Child $child): self
    {
        $this->child = $child;
        return $this;
    }
    public function getFeature(): string
    {
        return $this->feature;
    }
    public function setFeature(string $f): self
    {
        $this->feature = $f;
        return $this;
    }
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    public function setEnabled(bool $e): self
    {
        $this->enabled = $e;
        return $this;
    }
    public function getLimits(): ?array
    {
        return $this->limits;
    }
    public function setLimits(?array $l): self
    {
        $this->limits = $l;
        return $this;
    }

}
