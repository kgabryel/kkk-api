<?php

namespace App\Validator\ExistsForUser;

use App\Entity\User;
use App\Repository\FindByIdInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ExistsForUser extends Constraint
{
    public const REPOSITORY_OPTION = 'repository';
    public const USER_OPTION = 'user';
    public const COLUMN_OPTION = 'column';
    public string $message;
    private FindByIdInterface $repository;
    private User $user;
    private string $column;

    public function __construct(array $options = [])
    {
        $this->message = '';
        $this->repository = $options[self::REPOSITORY_OPTION];
        $this->user = $options[self::USER_OPTION];
        $this->column = $options[self::COLUMN_OPTION];
        parent::__construct($this->clearOptionsArray($options));
    }

    private function clearOptionsArray(array $options): array
    {
        unset($options[self::REPOSITORY_OPTION], $options[self::USER_OPTION], $options[self::COLUMN_OPTION]);

        return $options;
    }

    public function getRepository(): FindByIdInterface
    {
        return $this->repository;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
