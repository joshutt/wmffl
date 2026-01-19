<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'nflteams')]
class NflTeam
{
    #[ORM\Id]
    #[ORM\Column(name: 'nflteam', length: 3)]
    private ?string $id = null;

    #[ORM\Column(name: 'name', length: 25)]
    private ?string $name = null;

    #[ORM\Column(name: 'nickname', length: 20)]
    private ?string $nickname = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;
        return $this;
    }
}
