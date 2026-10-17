<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
class Season
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\OneToOne(targetEntity: Ingredient::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Ingredient $ingredient;

    #[ORM\Column(type: Types::INTEGER)]
    private int $start;

    #[ORM\Column(type: Types::INTEGER)]
    private int $stop;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIngredient(): Ingredient
    {
        return $this->ingredient;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getStop(): int
    {
        return $this->stop;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setIngredient(Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function setStart(int $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function setStop(int $stop): self
    {
        $this->stop = $stop;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
