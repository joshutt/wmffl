<?php

namespace App\Entity;

use App\Enum\ActiveEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User
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

    #[ORM\Column(name: 'Password', length: 50)]
    private ?string $password = null;

    #[ORM\Column(name: 'Name', length: 50, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'Email', length: 75)]
    private ?string $email = null;

    #[ORM\Column(name: 'primaryowner', type: 'boolean')]
    private ?bool $primaryOwner = false;

    #[ORM\Column(name: 'lastlog', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastLog = null;

    #[ORM\Column(name: 'blogaddress', length: 75, nullable: true)]
    private ?string $blogAddress = null;

    #[ORM\Column(name: 'active', enumType: ActiveEnum::class)]
    private ?ActiveEnum $active = ActiveEnum::Y;

    #[ORM\Column(name: 'commish', type: 'boolean')]
    private ?bool $commish = false;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
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

    public function getBlogAddress(): ?string
    {
        return $this->blogAddress;
    }

    public function setBlogAddress(?string $blogAddress): static
    {
        $this->blogAddress = $blogAddress;
        return $this;
    }

    public function getActive(): ?ActiveEnum
    {
        return $this->active;
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
}
