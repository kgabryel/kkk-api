<?php

namespace App\ValidatorRule\UniqueEmail;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    /** @var ServiceEntityRepository<User> */
    private ServiceEntityRepository $repository;

    /**
     * @param ServiceEntityRepository<User> $repository
     */
    public function __construct(ServiceEntityRepository $repository)
    {
        parent::__construct([]);
        $this->repository = $repository;
    }

    /**
     * @return ServiceEntityRepository<User>
     */
    public function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
