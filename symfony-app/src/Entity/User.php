<?php

namespace App\Entity;

use App\Enum\ActiveEnum;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A site login. Symfony Security authenticates against this entity
 * (Phase 18 item 1); legacy pages see the result through the $_SESSION
 * projection written by App\Security\LegacySessionProjector.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'UserID', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID', nullable: true)]
    private ?Team $team = null;

    #[ORM\Column(name: 'Username', length: 20)]
    private ?string $username = null;

    /** A modern hash, a legacy unsalted MD5 (32 hex chars), or '' (cannot log in). */
    #[ORM\Column(name: 'Password', length: 255)]
    private ?string $password = null;

    #[ORM\Column(name: 'Name', length: 50, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'primaryowner', type: 'boolean')]
    private ?bool $primaryOwner = false;

    #[ORM\Column(name: 'lastlog', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastLog = null;

    #[ORM\Column(name: 'active', enumType: ActiveEnum::class)]
    private ?ActiveEnum $active = ActiveEnum::Y;

    #[ORM\Column(name: 'commish', type: 'boolean')]
    private ?bool $commish = false;

    /** @var Collection<int, UserEmail> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserEmail::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC', 'id' => 'ASC'])]
    private Collection $emails;

    public function __construct()
    {
        $this->emails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * The user's address rows, oldest first. Change them only through
     * UserEmailService, which enforces the primary/limit rules.
     *
     * @return Collection<int, UserEmail>
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    /** @return string[] every address, oldest first (all of them receive mail) */
    public function getEmailAddresses(): array
    {
        return array_values($this->emails->map(static fn (UserEmail $e) => $e->getEmail())->toArray());
    }

    public function getPrimaryEmail(): ?string
    {
        foreach ($this->emails as $email) {
            if ($email->isPrimary()) {
                return $email->getEmail();
            }
        }

        return null;
    }

    public function isPrimaryOwner(): ?bool
    {
        return $this->primaryOwner;
    }

    public function setPrimaryOwner(bool $primaryOwner): static
    {
        $this->primaryOwner = $primaryOwner;
        return $this;
    }

    public function getLastLog(): ?\DateTime
    {
        return $this->lastLog;
    }

    public function setLastLog(?\DateTime $lastLog): static
    {
        $this->lastLog = $lastLog;
        return $this;
    }

    public function getActive(): ?ActiveEnum
    {
        return $this->active;
    }

    public function isActive(): bool
    {
        return $this->active === ActiveEnum::Y;
    }

    public function setActive(ActiveEnum $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function isCommish(): ?bool
    {
        return $this->commish;
    }

    public function setCommish(bool $commish): static
    {
        $this->commish = $commish;
        return $this;
    }

    /**
     * Which kind of hash this user has, for the admin list (never the hash
     * itself): 'modern', 'legacy' (unsalted MD5, upgraded at next login) or
     * 'none' (cannot log in until a set-password link is used).
     */
    public function getPasswordHashStatus(): string
    {
        $password = (string) $this->password;

        return match (true) {
            $password === '' => 'none',
            (bool) preg_match('/^[0-9a-f]{32}$/i', $password) => 'legacy',
            default => 'modern',
        };
    }

    // ---- Symfony Security ----

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];
        if ($this->commish) {
            $roles[] = 'ROLE_COMMISH';
        }

        return $roles;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    /**
     * What goes into the session with the security token: just enough to
     * reload the user (ContextListener refreshes it from the DB on every
     * request) and detect a changed password. Keeps Doctrine proxies and
     * collections out of the session, and stores only a checksum of the hash.
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => hash('crc32c', (string) $this->password),
            'commish' => $this->commish,
            'active' => $this->active?->value,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->emails = new ArrayCollection();
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->commish = $data['commish'] ?? false;
        $this->active = isset($data['active']) ? ActiveEnum::from($data['active']) : null;
    }
}
