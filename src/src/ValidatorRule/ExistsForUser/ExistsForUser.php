<?php

namespace App\ValidatorRule\ExistsForUser;

use App\Entity\User;
use App\Repository\FindByIdInterface;
use Symfony\Component\Validator\Constraint;

class ExistsForUser extends Constraint
{
    private FindByIdInterface $repository;
    private User $user;

    public function __construct(FindByIdInterface $repository, User $user)
    {
        parent::__construct([]);
        $this->repository = $repository;
        $this->user = $user;
    }

    public function getRepository(): FindByIdInterface
    {
        return $this->repository;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
