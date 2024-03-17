<?php

namespace App\Repository;

use App\Entity\User;

interface FindOneByNameWithLowercaseInterface
{
    public function findOneByNameWithLowercase(User $user, string $columnName, string $value): mixed;
}
