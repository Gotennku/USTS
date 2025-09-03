<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BackOfficeStat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $metric;

    #[ORM\Column(type: 'float')]
    private float $value;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $date;

    public function __construct()
    {
        $this->date = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getMetric(): string
    {
        return $this->metric;
    }
    public function setMetric(string $m): self
    {
        $this->metric = $m;
        return $this;
    }
    public function getValue(): float
    {
        return $this->value;
    }
    public function setValue(float $v): self
    {
        $this->value = $v;
        return $this;
    }
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }
    public function setDate(DateTimeImmutable $d): self
    {
        $this->date = $d;
        return $this;
    }
}
