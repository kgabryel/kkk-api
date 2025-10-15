<?php

namespace App\Entity;

use App\Repository\TimerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimerRepository::class)]
class Timer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $name;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'timers')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Recipe $recipe;

    #[ORM\Column(type: Types::INTEGER)]
    private int $time;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'timers')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function setTime(int $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
