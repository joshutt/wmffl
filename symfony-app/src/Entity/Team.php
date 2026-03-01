<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'team')]
class Team
{
    #[ORM\Id]
    #[ORM\Column(name: 'TeamID', type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: 'DivisionID', type: 'integer')]
    private int $division;

    #[ORM\Column(name: 'Name', type: 'string')]
    private string $name;

    #[ORM\Column(name: 'member', type: 'integer')]
    private int $since;

    #[ORM\Column(name: 'logo', type: 'string')]
    private string|null $logo;

    #[ORM\Column(name: 'fulllogo', type: 'boolean')]
    private bool $fullLogo;

    #[ORM\Column(name: 'abbrev', type: 'string')]
    private string $abbreviation;

    #[ORM\Column(name: 'active', type: 'boolean')]
    private bool $active;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDivision(): ?int
    {
        return $this->division;
    }

    public function setDivision(int $division): static
    {
        $this->division = $division;

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

    public function getSince(): ?int
    {
        return $this->since;
    }

    public function setSince(int $since): static
    {
        $this->since = $since;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function isFullLogo(): ?bool
    {
        return $this->fullLogo;
    }

    public function setFullLogo(bool $fullLogo): static
    {
        $this->fullLogo = $fullLogo;

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

}