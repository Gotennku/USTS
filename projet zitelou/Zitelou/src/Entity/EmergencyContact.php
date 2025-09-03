<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EmergencyContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'emergencyContacts', targetEntity: Child::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 30)]
    private string $phoneNumber;

    public function getId(): ?int { return $this->id; }
    public function getChild(): ?Child { return $this->child; }
    public function setChild(?Child $child): self { $this->child = $child; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $n): self { $this->name = $n; return $this; }
    public function getPhoneNumber(): string { return $this->phoneNumber; }
    public function setPhoneNumber(string $p): self { $this->phoneNumber = $p; return $this; }
}
