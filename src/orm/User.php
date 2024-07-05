<?php
namespace WMFFL\orm;

use Doctrine\ORM\Mapping as ORM;
use WMFFL\enum\YNEnumType;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User
{
    #[ORM\Id]
    #[ORM\Column(name: 'UserID', type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: 'TeamID', referencedColumnName: 'TeamID')]
    private Team|null $team;

    #[ORM\Column(name: 'Username', type: 'string')]
    private string $username;

    #[ORM\Column(name: 'Name', type: 'string')]
    private string $name;

    #[ORM\Column(name: 'Password', type: 'string')]
    private string $password;

    #[ORM\Column(name: 'Email', type: 'string')]
    private string $email;

    #[ORM\Column(name: 'primaryowner', type:'boolean')]
    private bool $primaryOwner;

    #[ORM\Column(name: 'lastlog', type: 'datetime')]
    private \DateTime $lastlog;

    #[ORM\Column(name: 'commish', type: 'boolean')]
    private bool $commish;

//    #[ORM\Column(name: 'active', type: 'ynenum')]
//    private YNEnumType $active;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $teamId): void
    {
        $this->team = $teamId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function isPrimaryOwner(): bool
    {
        return $this->primaryOwner;
    }

    public function setPrimaryOwner(bool $primaryOwner): void
    {
        $this->primaryOwner = $primaryOwner;
    }

    public function getLastlog(): \DateTime
    {
        return $this->lastlog;
    }

    public function setLastlog(\DateTime $lastlog): void
    {
        $this->lastlog = $lastlog;
    }

    public function isCommish(): bool
    {
        return $this->commish;
    }

    public function setCommish(bool $commish): void
    {
        $this->commish = $commish;
    }

//    public function getActive(): YNEnumType
//    {
//        return $this->active;
//    }
//
//    public function setActive(YNEnumType $active): void
//    {
//        $this->active = $active;
//    }


}