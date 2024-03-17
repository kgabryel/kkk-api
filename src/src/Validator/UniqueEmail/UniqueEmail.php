<?php

namespace App\Validator\UniqueEmail;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Validator\Constraint;

class UniqueEmail extends Constraint
{
    private ServiceEntityRepository $repository;

    public function __construct(ServiceEntityRepository $repository, array $options = [])
    {
        $this->repository = $repository;
        parent::__construct($options);
    }

    public function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
