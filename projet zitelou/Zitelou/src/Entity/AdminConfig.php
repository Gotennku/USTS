<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AdminConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $key;

    #[ORM\Column(type: 'text')]
    private string $value;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getKey(): string
    {
        return $this->key;
    }
    public function setKey(string $k): self
    {
        $this->key = $k;
        return $this;
    }
    public function getValue(): string
    {
        return $this->value;
    }
    public function setValue(string $v): self
    {
        $this->value = $v;
        return $this;
    }
}
