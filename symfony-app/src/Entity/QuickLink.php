<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * An admin-managed homepage "Other Links" entry. Replaces the static
 * list hand-edited each season in football/quicklinks.php: each link
 * carries an optional [startDate, endDate] window (inclusive, either
 * bound open-ended when null) controlling when it appears.
 */
#[ORM\Entity]
#[ORM\Table(name: 'quicklinks')]
class QuickLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'label', length: 100)]
    private ?string $label = null;

    #[ORM\Column(name: 'url', length: 255)]
    private ?string $url = null;

    #[ORM\Column(name: 'start_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(name: 'end_date', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $endDate = null;

    #[ORM\Column(name: 'active', type: 'boolean')]
    private bool $active = true;

    #[ORM\Column(name: 'sort_order', type: 'integer')]
    private int $sortOrder = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * Whether the link shows on the given day: active, and the day falls
     * inside the inclusive [startDate, endDate] window. Must stay in sync
     * with the SQL filter in QuickLinkRepository::findVisible().
     */
    public function isVisibleOn(\DateTimeInterface $day): bool
    {
        if (!$this->active) {
            return false;
        }
        $ymd = $day->format('Y-m-d');
        if ($this->startDate !== null && $this->startDate->format('Y-m-d') > $ymd) {
            return false;
        }
        if ($this->endDate !== null && $this->endDate->format('Y-m-d') < $ymd) {
            return false;
        }
        return true;
    }
}
