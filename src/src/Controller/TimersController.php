<?php

namespace App\Controller;

use App\Dto\Timer;
use App\Factory\Entity\TimerFactory;
use App\Form\TimerForm;
use App\Repository\TimerRepository;
use App\Service\Entity\TimerService;
use App\Service\SerializeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TimersController extends BaseController
{
    private SerializeService $serializer;

    public function __construct()
    {
        $this->serializer = SerializeService::getInstance(Timer::class);
    }

    public function index(TimerRepository $timerRepository): Response
    {
        return new Response($this->serializer->serializeArray($timerRepository->findForUser($this->getUser())));
    }

    public function store(TimerFactory $timerFactory, Request $request): Response
    {
        $form = $this->createForm(TimerForm::class);
        $timer = $timerFactory->create($form, $request);
        if ($timer === null) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($timer));
    }

    public function update(int $id, Request $request, TimerService $timerService): Response
    {
        if (!$timerService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(TimerForm::class, null, [
            self::METHOD => Request::METHOD_PUT
        ]);
        if (!$timerService->update($form, $request)) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($timerService->getTimer()), Response::HTTP_OK);
    }

    public function destroy(int $id, TimerService $timerService): Response
    {
        if (!$timerService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $timerService->remove();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
