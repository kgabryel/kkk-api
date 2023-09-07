<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ingredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ingredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ingredient[]    findAll()
 * @method Ingredient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IngredientRepository extends ServiceEntityRepository implements FindByIdInterface, FindOneByNameWithLowercaseInterface
{
    use FindTrait;
    use FindOneByNameWithLowercaseTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    /**
     * @param  User  $user
     *
     * @return Ingredient[]
     */
    public function findIngredientsWithoutSeason(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->leftJoin('i.season', 's')
            ->where('i.user = :user_id')
            ->andWhere('s.id is null')
            ->setParameter('user_id', $user->getId())
            ->getQuery()
            ->getResult();
    }

    public function findFirstIngredientWithOza(User $user): ?Ingredient
    {
        return $this->createQueryBuilder('i')
            ->select('i')
            ->where('i.user = :user_id')
            ->andWhere('i.ozaId is not null')
            ->setParameter('user_id', $user->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
