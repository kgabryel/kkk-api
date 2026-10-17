<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[] findAll()
 * @method Recipe[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository implements FindByIdInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function findById(int $id, User $user): ?Recipe
    {
        return $this->getFindQuery()
            ->where('recipe.id = :id')
            ->andWhere('recipe.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findForUser(User $user): array
    {
        return $this->getFindQuery()
            ->where('recipe.user = :user')
            ->setParameter('user', $user)
            ->orderBy('recipe.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    private function getFindQuery(): QueryBuilder
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('recipe', 'photos', 'timers', 'groups', 'tags', 'positions')
            ->from(Recipe::class, 'recipe')
            ->leftJoin('recipe.photos', 'photos')
            ->leftJoin('recipe.timers', 'timers')
            ->leftJoin('recipe.recipePositionGroups', 'groups')
            ->leftJoin('groups.recipePosition', 'positions')
            ->leftJoin('recipe.tags', 'tags');
    }
}
