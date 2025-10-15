<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ingredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ingredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ingredient[] findAll()
 * @method Ingredient[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Ingredient>
 */
class IngredientRepository extends ServiceEntityRepository implements
    FindByIdInterface,
    FindOneByNameWithLowercaseInterface
{
    use FindOneByNameWithLowercaseTrait;
    use FindTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ingredient::class);
    }

    public function findById(int $id, User $user): ?Ingredient
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
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

    public function resetOzaIdsForUser(User $user): int
    {
        return $this->createQueryBuilder('i')
            ->update()
            ->set('i.ozaId', ':null')
            ->where('i.user = :user')
            ->setParameter('null', null)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
