<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Column(type: Types::STRING, length: 36)]
    private string $fileName;

    #[ORM\Column(type: Types::INTEGER)]
    private int $height;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $photoOrder;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Recipe $recipe;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $type;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::INTEGER)]
    private int $width;

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPhotoOrder(): ?int
    {
        return $this->photoOrder;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function setPhotoOrder(?int $photoOrder): self
    {
        $this->photoOrder = $photoOrder;

        return $this;
    }

    public function setRecipe(Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }
}
