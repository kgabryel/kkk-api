<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $autocomplete;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    private ?string $ozaKey;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'settings')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function getAutocomplete(): bool
    {
        return $this->autocomplete;
    }

    public function getOzaKey(): ?string
    {
        return $this->ozaKey;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setAutocomplete(bool $autocomplete): self
    {
        $this->autocomplete = $autocomplete;

        return $this;
    }

    public function setOzaKey(?string $ozaKey): self
    {
        $this->ozaKey = $ozaKey;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
