<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $action;

    #[ORM\Column(length: 100)]
    private string $actor;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    public function __construct()
    { $this->date = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $a): self { $this->action = $a; return $this; }
    public function getActor(): string { return $this->actor; }
    public function setActor(string $a): self { $this->actor = $a; return $this; }
    public function getDate(): \DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $d): self { $this->date = $d; return $this; }
}
