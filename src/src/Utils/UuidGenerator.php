<?php

namespace App\Utils;

use Ramsey\Uuid\Uuid;

class UuidGenerator
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
