<?php

namespace App\Entity;

use App\Repository\ChildRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChildRepository::class)]
#[ORM\Table(name: '`child`')]
class Child
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: AuthorizedContact::class, orphanRemoval: true)]
    /** @var \Doctrine\Common\Collections\Collection<int, AuthorizedContact> */
    private $authorizedContacts;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: AuthorizedApp::class, orphanRemoval: true)]
    /** @var \Doctrine\Common\Collections\Collection<int, AuthorizedApp> */
    private $authorizedApps;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: FeatureAccess::class, orphanRemoval: true)]
    /** @var \Doctrine\Common\Collections\Collection<int, FeatureAccess> */
    private $featureAccesses;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: EmergencyContact::class, orphanRemoval: true)]
    /** @var \Doctrine\Common\Collections\Collection<int, EmergencyContact> */
    private $emergencyContacts;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: EmergencyCall::class, orphanRemoval: true)]
    /** @var \Doctrine\Common\Collections\Collection<int, EmergencyCall> */
    private $emergencyCalls;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: GeoLocation::class, orphanRemoval: true)]
    /** @var \Doctrine\Common\Collections\Collection<int, GeoLocation> */
    private $geoLocations;

    public function __construct()
    {
        $this->authorizedContacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->authorizedApps = new \Doctrine\Common\Collections\ArrayCollection();
        $this->featureAccesses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->emergencyContacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->emergencyCalls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->geoLocations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getParent(): ?User
    {
        return $this->parent;
    }
    public function setParent(?User $parent): self
    {
        $this->parent = $parent;
        return $this;
    }
    public function getFirstname(): string
    {
        return $this->firstname;
    }
    public function setFirstname(string $v): self
    {
        $this->firstname = $v;
        return $this;
    }
    public function getAge(): int
    {
        return $this->age;
    }
    public function setAge(int $age): self
    {
        $this->age = $age;
        return $this;
    }
    public function getPreferences(): ?array
    {
        return $this->preferences;
    }
    public function setPreferences(?array $p): self
    {
        $this->preferences = $p;
        return $this;
    }

    /** @return \Doctrine\Common\Collections\Collection<int, AuthorizedContact> */
    public function getAuthorizedContacts()
    {
        return $this->authorizedContacts;
    }
    public function addAuthorizedContact(AuthorizedContact $c): self
    {
        if (!$this->authorizedContacts->contains($c)) {
            $this->authorizedContacts->add($c);
            $c->setChild($this);
        } return $this;
    }
    public function removeAuthorizedContact(AuthorizedContact $c): self
    {
        if ($this->authorizedContacts->removeElement($c) && $c->getChild() === $this) {
            $c->setChild(null);
        } return $this;
    }

    /** @return \Doctrine\Common\Collections\Collection<int, AuthorizedApp> */
    public function getAuthorizedApps()
    {
        return $this->authorizedApps;
    }
    public function addAuthorizedApp(AuthorizedApp $a): self
    {
        if (!$this->authorizedApps->contains($a)) {
            $this->authorizedApps->add($a);
            $a->setChild($this);
        } return $this;
    }
    public function removeAuthorizedApp(AuthorizedApp $a): self
    {
        if ($this->authorizedApps->removeElement($a) && $a->getChild() === $this) {
            $a->setChild(null);
        } return $this;
    }

    /** @return \Doctrine\Common\Collections\Collection<int, FeatureAccess> */
    public function getFeatureAccesses()
    {
        return $this->featureAccesses;
    }
    public function addFeatureAccess(FeatureAccess $f): self
    {
        if (!$this->featureAccesses->contains($f)) {
            $this->featureAccesses->add($f);
            $f->setChild($this);
        } return $this;
    }
    public function removeFeatureAccess(FeatureAccess $f): self
    {
        if ($this->featureAccesses->removeElement($f) && $f->getChild() === $this) {
            $f->setChild(null);
        } return $this;
    }

    /** @return \Doctrine\Common\Collections\Collection<int, EmergencyContact> */
    public function getEmergencyContacts()
    {
        return $this->emergencyContacts;
    }
    public function addEmergencyContact(EmergencyContact $e): self
    {
        if (!$this->emergencyContacts->contains($e)) {
            $this->emergencyContacts->add($e);
            $e->setChild($this);
        } return $this;
    }
    public function removeEmergencyContact(EmergencyContact $e): self
    {
        if ($this->emergencyContacts->removeElement($e) && $e->getChild() === $this) {
            $e->setChild(null);
        } return $this;
    }

    /** @return \Doctrine\Common\Collections\Collection<int, EmergencyCall> */
    public function getEmergencyCalls()
    {
        return $this->emergencyCalls;
    }
    public function addEmergencyCall(EmergencyCall $e): self
    {
        if (!$this->emergencyCalls->contains($e)) {
            $this->emergencyCalls->add($e);
            $e->setChild($this);
        } return $this;
    }
    public function removeEmergencyCall(EmergencyCall $e): self
    {
        if ($this->emergencyCalls->removeElement($e) && $e->getChild() === $this) {
            $e->setChild(null);
        } return $this;
    }

    /** @return \Doctrine\Common\Collections\Collection<int, GeoLocation> */
    public function getGeoLocations()
    {
        return $this->geoLocations;
    }
    public function addGeoLocation(GeoLocation $g): self
    {
        if (!$this->geoLocations->contains($g)) {
            $this->geoLocations->add($g);
            $g->setChild($this);
        } return $this;
    }
    public function removeGeoLocation(GeoLocation $g): self
    {
        if ($this->geoLocations->removeElement($g) && $g->getChild() === $this) {
            $g->setChild(null);
        } return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $parent = null;

    #[ORM\Column(length: 100)]
    private string $firstname;

    #[ORM\Column(type: 'integer')]
    private int $age;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $preferences = [];

}
