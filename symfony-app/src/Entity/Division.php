<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Maps to the division table
 * Stores division information
 */
#[ORM\Entity]
#[ORM\Table(name: 'division')]
class Division
{
    #[ORM\Id]
    #[ORM\Column(name: 'divisionid', type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 100)]
    private string $name;

    #[ORM\Column(name: 'startYear', type: Types::INTEGER)]
    private int $startYear;

    #[ORM\Column(name: 'endYear', type: Types::INTEGER, nullable: true)]
    private ?int $endYear = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getStartYear(): int
    {
        return $this->startYear;
    }

    public function setStartYear(int $startYear): static
    {
        $this->startYear = $startYear;
        return $this;
    }

    public function getEndYear(): ?int
    {
        return $this->endYear;
    }

    public function setEndYear(?int $endYear): static
    {
        $this->endYear = $endYear;
        return $this;
    }
}
