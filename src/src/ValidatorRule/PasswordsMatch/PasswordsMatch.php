<?php

namespace App\ValidatorRule\PasswordsMatch;

use Symfony\Component\Validator\Constraint;

class PasswordsMatch extends Constraint
{
    private string $key;

    public function __construct(string $key)
    {
        parent::__construct([]);
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
