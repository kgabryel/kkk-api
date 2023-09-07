<?php

namespace App\Validator\UniqueEmail;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    public const REPOSITORY_OPTION = 'repository';
    private ServiceEntityRepositoryInterface $repository;

    public function __construct(array $options = [])
    {
        $this->repository = $options[self::REPOSITORY_OPTION];
        parent::__construct(self::clearOptionsArray($options));
    }

    private static function clearOptionsArray(array $options): array
    {
        unset($options[self::REPOSITORY_OPTION]);

        return $options;
    }

    public function getRepository(): ServiceEntityRepositoryInterface
    {
        return $this->repository;
    }
}
