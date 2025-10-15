<?php

namespace App\Controller;

use App\Entity\Timer;
use App\Factory\Entity\TimerFactory;
use App\Repository\TimerRepository;
use App\Response\TimerListResponse;
use App\Response\TimerResponse;
use App\Service\Entity\TimerService;
use App\Validation\TimerValidation;
use Symfony\Component\HttpFoundation\Response;

class TimersController extends BaseController
{
    public function destroy(int $id, TimerService $timerService): Response
    {
        $timer = $timerService->find($id);
        if (!($timer instanceof Timer)) {
            return $this->getNotFoundResponse();
        }

        $timerService->remove($timer);

        return $this->getNoContentResponse();
    }

    public function index(TimerRepository $timerRepository): TimerListResponse
    {
        return new TimerListResponse($this->dtoFactoryDispatcher, ...$timerRepository->findForUser($this->getUser()));
    }

    public function store(TimerFactory $timerFactory, TimerValidation $timerValidation): Response
    {
        $timer = $timerFactory->create($timerValidation);
        if (!($timer instanceof Timer)) {
            return $this->getBadRequestResponse();
        }

        return new TimerResponse($this->dtoFactoryDispatcher, $timer, Response::HTTP_CREATED);
    }

    public function update(int $id, TimerValidation $timerValidation, TimerService $timerService): Response
    {
        $timer = $timerService->find($id);
        if (!($timer instanceof Timer)) {
            return $this->getNotFoundResponse();
        }

        if (!$timerService->update($timer, $timerValidation)) {
            return $this->getBadRequestResponse();
        }

        return new TimerResponse($this->dtoFactoryDispatcher, $timer, Response::HTTP_OK);
    }
}
