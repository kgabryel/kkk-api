<?php

namespace App\Repository;

use App\Entity\User;

interface FindByIdInterface
{
    public function findById(int $id, User $user): mixed;
}
