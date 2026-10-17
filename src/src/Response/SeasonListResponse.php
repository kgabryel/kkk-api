<?php

namespace App\Response;

use App\Dto\Entity\List\SeasonList;
use App\Entity\Season;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class SeasonListResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Season ...$seasons)
    {
        parent::__construct($dtoFactory->getMany(SeasonList::class, ...$seasons));
    }
}
