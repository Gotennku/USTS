<?php

namespace App\Entity;

use App\Enum\EmergencyCallStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EmergencyCall
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'emergencyCalls', targetEntity: Child::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Child $child = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $timestamp;

    #[ORM\Column(enumType: EmergencyCallStatus::class)]
    private EmergencyCallStatus $status; // enum

    public function __construct()
    {
        $this->timestamp = new DateTimeImmutable();
    }

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
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
    public function setTimestamp(DateTimeImmutable $t): self
    {
        $this->timestamp = $t;
        return $this;
    }
    public function getStatus(): EmergencyCallStatus
    {
        return $this->status;
    }
    public function setStatus(EmergencyCallStatus $s): self
    {
        $this->status = $s;
        return $this;
    }
}
