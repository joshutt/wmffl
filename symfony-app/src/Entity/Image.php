<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'images')]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'url', length: 40)]
    private ?string $url = null;

    #[ORM\Column(name: 'fullImage', type: Types::BLOB, nullable: true)]
    private $fullImage = null;

    #[ORM\Column(name: 'smallImage', type: Types::BLOB, nullable: true)]
    private $smallImage = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFullImage()
    {
        return $this->fullImage;
    }

    public function setFullImage($fullImage): static
    {
        $this->fullImage = $fullImage;
        return $this;
    }

    public function getSmallImage()
    {
        return $this->smallImage;
    }

    public function setSmallImage($smallImage): static
    {
        $this->smallImage = $smallImage;
        return $this;
    }
}
