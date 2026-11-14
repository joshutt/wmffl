<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * One email address on a user (Phase 18 item 1). A user may have several;
 * exactly one is primary whenever there are any, and every address gets all
 * league mail and security notices. The rules live in UserEmailService.
 */
#[ORM\Entity]
#[ORM\Table(name: 'user_email')]
#[ORM\UniqueConstraint(name: 'uniq_user_email_user_address', columns: ['user_id', 'email'])]
class UserEmail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'emails')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'UserID', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(name: 'is_primary', type: 'boolean')]
    private bool $primary = false;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user, string $email, ?\DateTimeImmutable $createdAt = null)
    {
        $this->user = $user;
        $this->email = $email;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    public function setPrimary(bool $primary): static
    {
        $this->primary = $primary;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
