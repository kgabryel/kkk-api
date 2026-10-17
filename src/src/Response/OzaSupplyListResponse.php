<?php

namespace App\Response;

use App\Dto\Entity\List\OzaSupplyList;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class OzaSupplyListResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, object ...$supplies)
    {
        parent::__construct($dtoFactory->getMany(OzaSupplyList::class, ...$supplies));
    }
}
