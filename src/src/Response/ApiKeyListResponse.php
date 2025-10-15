<?php

namespace App\Response;

use App\Dto\Entity\List\ApiKeyList;
use App\Entity\ApiKey;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiKeyListResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, ApiKey ...$apiKeys)
    {
        parent::__construct($dtoFactory->getMany(ApiKeyList::class, ...$apiKeys));
    }
}
