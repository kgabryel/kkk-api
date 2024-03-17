<?php

namespace App\Validator\UniqueNameForUser;

use App\Entity\User;
use App\Repository\FindOneByNameWithLowercaseInterface;
use Symfony\Component\Validator\Constraint;

class UniqueNameForUser extends Constraint
{
    private FindOneByNameWithLowercaseInterface $repository;
    private User $user;
    private string $column;
    private int $expect;

    public function __construct(
        FindOneByNameWithLowercaseInterface $repository,
        User $user,
        string $column,
        int $expect = 0,
        array $options = []
    ) {
        $this->repository = $repository;
        $this->user = $user;
        $this->column = $column;
        $this->expect = $expect;
        parent::__construct($options);
    }

    public function getRepository(): FindOneByNameWithLowercaseInterface
    {
        return $this->repository;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getExpect(): int
    {
        return $this->expect;
    }
}
