<?php

namespace App\Service\Entity;

use App\Entity\Timer;
use App\Repository\TimerRepository;
use App\Service\UserService;
use App\Validation\TimerValidation;
use Doctrine\ORM\EntityManagerInterface;

class TimerService extends EntityService
{
    private TimerRepository $timerRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        TimerRepository $timerRepository,
    ) {
        parent::__construct($entityManager, $userService);
        $this->timerRepository = $timerRepository;
    }

    public function find(int $id): ?Timer
    {
        return $this->timerRepository->findById($id, $this->user);
    }

    public function remove(Timer $timer): void
    {
        $this->removeEntity($timer);
    }

    public function update(Timer $timer, TimerValidation $timerValidation): bool
    {
        if (!$timerValidation->validate()->passed()) {
            return false;
        }
        $data = $timerValidation->getDto();
        $timer->setName($data->getName());
        $timer->setTime($data->getTime());
        $this->saveEntity($timer);

        return true;
    }
}
