<?php

namespace App\Validator\UniqueNameForUser;

use App\Entity\User;
use App\Repository\FindOneByNameWithLowercaseInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueNameForUser extends Constraint
{
    public const REPOSITORY_OPTION = 'repository';
    public const USER_OPTION = 'user';
    public const COLUMN_OPTION = 'column';
    public const EXPECT_OPTION = 'expect';
    public string $message;
    private FindOneByNameWithLowercaseInterface $repository;
    private User $user;
    private string $column;
    private int $expect;

    public function __construct(array $options = [])
    {
        $this->message = '';
        $this->repository = $options[self::REPOSITORY_OPTION];
        $this->user = $options[self::USER_OPTION];
        $this->column = $options[self::COLUMN_OPTION];
        $this->expect = $options[self::EXPECT_OPTION] ?? 0;
        parent::__construct($this->clearOptionsArray($options));
    }

    private function clearOptionsArray(array $options): array
    {
        unset($options[self::REPOSITORY_OPTION], $options[self::USER_OPTION], $options[self::COLUMN_OPTION], $options[self::EXPECT_OPTION]);

        return $options;
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
