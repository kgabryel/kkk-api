<?php

namespace App\ValidatorRule\UniqueNameForUser;

use App\Entity\User;
use App\Repository\FindOneByNameWithLowercaseInterface;
use Symfony\Component\Validator\Constraint;

class UniqueNameForUser extends Constraint
{
    private string $column;
    private int $expect;
    private FindOneByNameWithLowercaseInterface $repository;
    private User $user;

    public function __construct(
        FindOneByNameWithLowercaseInterface $repository,
        User $user,
        string $column,
        int $expect = 0,
    ) {
        parent::__construct([]);
        $this->repository = $repository;
        $this->user = $user;
        $this->column = $column;
        $this->expect = $expect;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getExpect(): int
    {
        return $this->expect;
    }

    public function getRepository(): FindOneByNameWithLowercaseInterface
    {
        return $this->repository;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
