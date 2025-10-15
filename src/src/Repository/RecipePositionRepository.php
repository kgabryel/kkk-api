<?php

namespace App\Repository;

use App\Entity\RecipePosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipePosition|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipePosition|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipePosition[] findAll()
 * @method RecipePosition[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<RecipePosition>
 */
class RecipePositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipePosition::class);
    }
}
