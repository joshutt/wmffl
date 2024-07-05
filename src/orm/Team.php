<?php

namespace WMFFL\orm;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDivision(): int
    {
        return $this->division;
    }

    public function setDivision(int $division): void
    {
        $this->division = $division;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSince(): int
    {
        return $this->since;
    }

    public function setSince(int $since): void
    {
        $this->since = $since;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    public function isFullLogo(): bool
    {
        return $this->fullLogo;
    }

    public function setFullLogo(bool $fullLogo): void
    {
        $this->fullLogo = $fullLogo;
    }

    public function getAbbreviation(): string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(string $abbreviation): void
    {
        $this->abbreviation = $abbreviation;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

}