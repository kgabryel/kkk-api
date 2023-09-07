<?php

namespace App\Repository;

use App\Entity\User;

trait FindOneByNameWithLowercaseTrait
{
    public function findOneByNameWithLowercase(User $user, string $columnName, ?string $value)
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->where('e.user = :user_id')
            ->andWhere(sprintf('lower(e.%s) = lower(:value)', $columnName))
            ->setParameter('user_id', $user->getId())
            ->setParameter('value', $value ?? '')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
