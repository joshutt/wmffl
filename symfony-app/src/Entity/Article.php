<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'articles')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'articleId', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'title', length: 75)]
    private ?string $title = null;

    #[ORM\Column(name: 'link', length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(name: 'caption', length: 255, nullable: true)]
    private ?string $caption = null;

    #[ORM\Column(name: 'location', length: 50, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(name: 'articleText', type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(name: 'displayDate', type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $displayDate = null;

    #[ORM\Column(name: 'active', type: 'boolean')]
    private ?bool $active = false;

    #[ORM\Column(name: 'priority', type: 'integer')]
    private ?int $priority = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'author', referencedColumnName: 'UserID', nullable: true)]
    private ?User $author = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getDisplayDate(): ?\DateTime
    {
        return $this->displayDate;
    }

    public function setDisplayDate(\DateTime $displayDate): static
    {
        $this->displayDate = $displayDate;
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

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }
}
