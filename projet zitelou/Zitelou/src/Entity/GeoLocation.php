<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class GeoLocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'geoLocations', targetEntity: Child::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    #[ORM\Column(type: 'float')]
    private float $latitude;

    #[ORM\Column(type: 'float')]
    private float $longitude;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $timestamp;

    public function __construct()
    { $this->timestamp = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getChild(): ?Child { return $this->child; }
    public function setChild(?Child $child): self { $this->child = $child; return $this; }
    public function getLatitude(): float { return $this->latitude; }
    public function setLatitude(float $v): self { $this->latitude = $v; return $this; }
    public function getLongitude(): float { return $this->longitude; }
    public function setLongitude(float $v): self { $this->longitude = $v; return $this; }
    public function getTimestamp(): \DateTimeImmutable { return $this->timestamp; }
    public function setTimestamp(\DateTimeImmutable $t): self { $this->timestamp = $t; return $this; }
}
