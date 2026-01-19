<?php

namespace App\Entity;

use App\Enum\DraftDateAttendEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'draftdate')]
class DraftDate
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'UserID', referencedColumnName: 'UserID')]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\Column(name: 'Date', type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(name: 'Attend', enumType: DraftDateAttendEnum::class)]
    private ?DraftDateAttendEnum $attend = DraftDateAttendEnum::Y;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getAttend(): ?DraftDateAttendEnum
    {
        return $this->attend;
    }

    public function setAttend(DraftDateAttendEnum $attend): static
    {
        $this->attend = $attend;
        return $this;
    }
}
