<?php

namespace App\Repository;

use App\Entity\User;

trait FindTrait
{
    public function findForUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['id' => 'DESC']
        );
    }

    public function findById(int $id, User $user)
    {
        return $this->findOneBy(compact('id', 'user'));
    }
}
