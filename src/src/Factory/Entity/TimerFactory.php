<?php

namespace App\Factory\Entity;

use App\Entity\Timer;
use App\Model\Timer as TimerModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TimerFactory extends EntityFactory
{
    public function create(FormInterface $form, Request $request): ?Timer
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }
        /** @var TimerModel $data */
        $data = $form->getData();
        $timer = new Timer();
        $timer->setUser($this->user);
        $timer->setName($data->getName());
        $timer->setTime($data->getTime());
        $this->saveEntity($timer);

        return $timer;
    }
}
