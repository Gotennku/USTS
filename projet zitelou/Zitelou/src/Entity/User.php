<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 255)]
    private string $password = '';

    #[ORM\Column(name: 'first_name', length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(name: 'last_name', length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTime $updatedAt;

    /** @var Collection<int, Child> */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Child::class, orphanRemoval: true)]
    private Collection $children;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: ParentalSettings::class, cascade: ['persist', 'remove'])]
    private ?ParentalSettings $parentalSettings = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Subscription::class, cascade: ['persist', 'remove'])]
    private ?Subscription $subscription = null;

    /** @var Collection<int, UserLog> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserLog::class, orphanRemoval: true)]
    private Collection $userLogs;

    /** @var Collection<int, AuthToken> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AuthToken::class, orphanRemoval: true)]
    private Collection $authTokens;

    /** @var Collection<int, PasswordResetToken> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PasswordResetToken::class, orphanRemoval: true)]
    private Collection $passwordResetTokens;

    /** @var Collection<int, AdminLog> */
    #[ORM\OneToMany(mappedBy: 'admin', targetEntity: AdminLog::class, orphanRemoval: true)]
    private Collection $adminLogs;

    /** @var Collection<int, BanList> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: BanList::class, orphanRemoval: true)]
    private Collection $banLists;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeCustomerId = null;

    public function __construct()
    {
        $now = new DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = new DateTime();
        $this->children = new ArrayCollection();
        $this->userLogs = new ArrayCollection();
        $this->authTokens = new ArrayCollection();
        $this->passwordResetTokens = new ArrayCollection();
        $this->adminLogs = new ArrayCollection();
        $this->banLists = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new DateTimeImmutable();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @return string[] */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        return array_values(array_unique($roles));
    }
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }
    public function setFirstName(?string $v): self
    {
        $this->firstName = $v;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }
    public function setLastName(?string $v): self
    {
        $this->lastName = $v;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $v): self
    {
        $this->isActive = $v;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(DateTimeImmutable $v): self
    {
        $this->createdAt = $v;
        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(DateTime $v): self
    {
        $this->updatedAt = $v;
        return $this;
    }

    /** @return Collection<int, Child> */
    public function getChildren(): Collection
    {
        return $this->children;
    }
    public function addChild(Child $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }
    public function removeChild(Child $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }

    public function getParentalSettings(): ?ParentalSettings
    {
        return $this->parentalSettings;
    }
    public function setParentalSettings(?ParentalSettings $parentalSettings): self
    {
        $this->parentalSettings = $parentalSettings;
        if ($parentalSettings && $parentalSettings->getUser() !== $this) {
            $parentalSettings->setUser($this);
        }
        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }
    public function setSubscription(?Subscription $subscription): self
    {
        $this->subscription = $subscription;
        if ($subscription && $subscription->getUser() !== $this) {
            $subscription->setUser($this);
        }
        return $this;
    }

    /** @return Collection<int, UserLog> */
    public function getUserLogs(): Collection
    {
        return $this->userLogs;
    }
    public function addUserLog(UserLog $log): self
    {
        if (!$this->userLogs->contains($log)) {
            $this->userLogs->add($log);
            $log->setUser($this);
        }
        return $this;
    }
    public function removeUserLog(UserLog $log): self
    {
        if ($this->userLogs->removeElement($log)) {
            if ($log->getUser() === $this) {
                $log->setUser(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, AuthToken> */
    public function getAuthTokens(): Collection
    {
        return $this->authTokens;
    }
    public function addAuthToken(AuthToken $token): self
    {
        if (!$this->authTokens->contains($token)) {
            $this->authTokens->add($token);
            $token->setUser($this);
        }
        return $this;
    }
    public function removeAuthToken(AuthToken $token): self
    {
        if ($this->authTokens->removeElement($token)) {
            if ($token->getUser() === $this) {
                $token->setUser(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, PasswordResetToken> */
    public function getPasswordResetTokens(): Collection
    {
        return $this->passwordResetTokens;
    }
    public function addPasswordResetToken(PasswordResetToken $t): self
    {
        if (!$this->passwordResetTokens->contains($t)) {
            $this->passwordResetTokens->add($t);
            $t->setUser($this);
        }
        return $this;
    }
    public function removePasswordResetToken(PasswordResetToken $t): self
    {
        if ($this->passwordResetTokens->removeElement($t)) {
            if ($t->getUser() === $this) {
                $t->setUser(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, AdminLog> */
    public function getAdminLogs(): Collection
    {
        return $this->adminLogs;
    }
    public function addAdminLog(AdminLog $log): self
    {
        if (!$this->adminLogs->contains($log)) {
            $this->adminLogs->add($log);
            $log->setAdmin($this);
        }
        return $this;
    }
    public function removeAdminLog(AdminLog $log): self
    {
        if ($this->adminLogs->removeElement($log)) {
            if ($log->getAdmin() === $this) {
                $log->setAdmin(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, BanList> */
    public function getBanLists(): Collection
    {
        return $this->banLists;
    }
    public function addBanList(BanList $ban): self
    {
        if (!$this->banLists->contains($ban)) {
            $this->banLists->add($ban);
            $ban->setUser($this);
        }
        return $this;
    }
    public function removeBanList(BanList $ban): self
    {
        if ($this->banLists->removeElement($ban)) {
            if ($ban->getUser() === $this) {
                $ban->setUser(null);
            }
        }
        return $this;
    }

    public function getStripeCustomerId(): ?string
    {
        return $this->stripeCustomerId;
    }
    public function setStripeCustomerId(?string $id): self
    {
        $this->stripeCustomerId = $id;
        return $this;
    }
}
