<?php

namespace App\Repository;

use App\Entity\Photo;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 *
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[] findAll()
 * @method Photo[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository implements FindByIdInterface
{
    use FindTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    public function findById(int $id, User $user): ?Photo
    {
        return $this->findOneBy(['id' => $id, 'user' => $user]);
    }

    public function getNextPhotoOrderForRecipe(Recipe $recipe): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('MAX(p.photoOrder)')
            ->where('p.recipe = :recipe')
            ->setParameter('recipe', $recipe);
        $maxOrder = $qb->getQuery()->getSingleScalarResult();

        return ($maxOrder !== null) ? ((int)$maxOrder + 1) : 1;
    }
}
