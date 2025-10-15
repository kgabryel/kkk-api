<?php

namespace App\Repository;

use App\Entity\RecipePositionGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipePositionGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipePositionGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipePositionGroup[] findAll()
 * @method RecipePositionGroup[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<RecipePositionGroup>
 */
class RecipePositionGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipePositionGroup::class);
    }
}
