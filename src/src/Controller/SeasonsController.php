<?php

namespace App\Controller;

use App\Dto\Season;
use App\Factory\Entity\SeasonFactory;
use App\Form\EditSeasonForm;
use App\Form\SeasonForm;
use App\Repository\SeasonRepository;
use App\Service\Entity\SeasonService;
use App\Service\SerializeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SeasonsController extends BaseController
{
    private SerializeService $serializer;

    public function __construct()
    {
        $this->serializer = SerializeService::getInstance(Season::class);
    }

    public function index(SeasonRepository $seasonRepository): Response
    {
        return new Response($this->serializer->serializeArray($seasonRepository->findForUser($this->getUser())));
    }

    public function store(SeasonFactory $seasonFactory, Request $request): Response
    {
        $form = $this->createForm(SeasonForm::class);
        $season = $seasonFactory->create($form, $request);
        if ($season === null) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($season));
    }

    public function modify(int $id, Request $request, SeasonService $seasonService): Response
    {
        if (!$seasonService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(EditSeasonForm::class, null, [
            self::METHOD => Request::METHOD_PATCH
        ]);
        if (!$seasonService->update($form, $request)) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($seasonService->getSeason()), Response::HTTP_OK);
    }

    public function destroy(int $id, SeasonService $seasonService): Response
    {
        if (!$seasonService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $seasonService->remove();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
