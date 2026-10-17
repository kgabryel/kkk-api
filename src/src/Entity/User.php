<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    #[ORM\Column(type: Types::STRING, length: 254, unique: true)]
    private string $email;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $fbId;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING)]
    private string $password;

    #[ORM\OneToOne(targetEntity: Settings::class, mappedBy: 'user', fetch: 'EAGER')]
    private Settings $settings;

    /**
     * @var Collection<int, Timer>
     */
    #[ORM\OneToMany(targetEntity: Timer::class, mappedBy: 'user', orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $timers;

    public function __construct()
    {
        $this->password = '';
        $this->fbId = null;
    }

    public static function createFromPayload($username, array $payload)
    {
        $user = new self();
        $user->setId((int)$username);

        return $user;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFbId(): ?string
    {
        return $this->fbId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return [];
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->id;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setFbId(?string $fbId): self
    {
        $this->fbId = $fbId;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    private function setId(int $id): void
    {
        $this->id = $id;
    }
}
