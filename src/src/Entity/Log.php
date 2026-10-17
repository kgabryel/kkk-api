<?php

namespace App\Entity;

use App\Repository\LogRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    #[ORM\Column(type: Types::TEXT)]
    private string $context;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::TEXT)]
    private string $extra;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getExtra(): string
    {
        return $this->extra;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    public function setExtra(string $extra): void
    {
        $this->extra = $extra;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
