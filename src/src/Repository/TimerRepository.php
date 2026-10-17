<?php

namespace App\Repository;

use App\Entity\Timer;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Timer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Timer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Timer[] findAll()
 * @method Timer[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Timer>
 */
class TimerRepository extends ServiceEntityRepository implements FindByIdInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Timer::class);
    }

    public function findById(int $id, User $user): ?Timer
    {
        return $this->findOneBy(
            [
                'id' => $id,
                'recipe' => null,
                'user' => $user,
            ],
        );
    }

    public function findForUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user, 'recipe' => null],
            ['id' => 'DESC'],
        );
    }
}
