<?php

namespace App\Service\Entity;

use App\Entity\Timer;
use App\Model\Timer as TimerModel;
use App\Repository\TimerRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TimerService extends EntityService
{
    private Timer $timer;
    private TimerRepository $timerRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        TimerRepository $timerRepository
    ) {
        parent::__construct($entityManager, $userService);
        $this->timerRepository = $timerRepository;
    }

    public function find(int $id): bool
    {
        $timer = $this->timerRepository->findById($id, $this->user);
        if ($timer === null) {
            return false;
        }
        $this->timer = $timer;

        return true;
    }

    public function getTimer(): Timer
    {
        return $this->timer;
    }

    public function update(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        /** @var TimerModel $data */
        $data = $form->getData();
        $this->timer->setName($data->getName());
        $this->timer->setTime($data->getTime());
        $this->saveEntity($this->timer);

        return true;
    }

    public function remove(): void
    {
        $this->removeEntity($this->timer);
    }
}
