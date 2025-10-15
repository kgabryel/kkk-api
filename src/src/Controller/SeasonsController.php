<?php

namespace App\Controller;

use App\Entity\Season;
use App\Factory\Entity\SeasonFactory;
use App\Repository\SeasonRepository;
use App\Response\SeasonListResponse;
use App\Response\SeasonResponse;
use App\Service\Entity\SeasonService;
use App\Validation\EditSeasonValidation;
use App\Validation\SeasonValidation;
use Symfony\Component\HttpFoundation\Response;

class SeasonsController extends BaseController
{
    public function destroy(int $id, SeasonService $seasonService): Response
    {
        $season = $seasonService->find($id);
        if (!($season instanceof Season)) {
            return $this->getNotFoundResponse();
        }

        $seasonService->remove($season);

        return $this->getNoContentResponse();
    }

    public function index(SeasonRepository $seasonRepository): SeasonListResponse
    {
        return new SeasonListResponse(
            $this->dtoFactoryDispatcher,
            ...$seasonRepository->findForUser($this->getUser()),
        );
    }

    public function modify(int $id, EditSeasonValidation $seasonValidation, SeasonService $seasonService): Response
    {
        $season = $seasonService->find($id);
        if (!($season instanceof Season)) {
            return $this->getNotFoundResponse();
        }

        if (!$seasonService->update($season, $seasonValidation)) {
            return $this->getBadRequestResponse();
        }

        return new SeasonResponse($this->dtoFactoryDispatcher, $season, Response::HTTP_OK);
    }

    public function store(SeasonFactory $seasonFactory, SeasonValidation $seasonValidation): Response
    {
        $season = $seasonFactory->create($seasonValidation);
        if (!($season instanceof Season)) {
            return $this->getBadRequestResponse();
        }

        return new SeasonResponse($this->dtoFactoryDispatcher, $season, Response::HTTP_CREATED);
    }
}
