<?php

namespace App\Model;

class NewPassword
{
    private ?string $newPassword;

    public function __construct()
    {
        $this->newPassword = null;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(?string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }
}
