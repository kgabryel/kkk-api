<?php

namespace App\Tests\Helper;

class EntityTestDataDto
{
    private array $entityData;
    private array $parameters;
    private string $userEmail;

    public function __construct(string $userEmail, array $entityData = [], array $parameters = [])
    {
        $this->userEmail = $userEmail;
        $this->entityData = $entityData;
        $this->parameters = $parameters;
    }

    public function clone(array $entityData = [], array $parameters = []): self
    {
        return new self(
            $this->userEmail,
            array_replace($this->entityData, $entityData),
            array_replace($this->parameters, $parameters),
        );
    }

    public function getEntityData(): array
    {
        return $this->entityData;
    }

    public function getParameter(string $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
