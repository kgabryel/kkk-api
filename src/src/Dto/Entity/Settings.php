<?php

namespace App\Dto\Entity;

use JsonSerializable;

class Settings implements DtoInterface, JsonSerializable
{
    private bool $autocomplete;
    private ?string $ozaKey;
    private string $userType;

    public function __construct(bool $autocomplete, ?string $ozaKey, bool $isStandardUser)
    {
        $this->autocomplete = $autocomplete;
        $this->ozaKey = $ozaKey;
        $this->userType = $isStandardUser ? 'standard' : 'facebook';
    }

    public function jsonSerialize(): array
    {
        return [
            'autocomplete' => $this->autocomplete,
            'ozaKey' => $this->ozaKey,
            'userType' => $this->userType,
        ];
    }
}
