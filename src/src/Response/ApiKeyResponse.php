<?php

namespace App\Response;

use App\Dto\Entity\ApiKey as Dto;
use App\Entity\ApiKey;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiKeyResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, ApiKey $apiKey, int $status)
    {
        parent::__construct($dtoFactory->get(Dto::class, $apiKey), $status);
    }
}
