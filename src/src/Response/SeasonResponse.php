<?php

namespace App\Response;

use App\Dto\Entity\Season as Dto;
use App\Entity\Season;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class SeasonResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Season $season, int $status)
    {
        parent::__construct($dtoFactory->get(Dto::class, $season), $status);
    }
}
