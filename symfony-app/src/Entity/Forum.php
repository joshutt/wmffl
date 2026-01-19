<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'forum')]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'forumid', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'userid', referencedColumnName: 'UserID')]
    private ?User $user = null;

    #[ORM\Column(name: 'title', length: 255)]
    private ?string $title = null;

    #[ORM\Column(name: 'body', type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\Column(name: 'createTime', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function getCreateTime(): ?\DateTime
    {
        return $this->createTime;
    }

    public function setCreateTime(\DateTime $createTime): static
    {
        $this->createTime = $createTime;
        return $this;
    }
}
