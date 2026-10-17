<?php

namespace App\Response;

use App\Dto\Entity\Timer as Dto;
use App\Entity\Timer;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class TimerResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Timer $timer, int $status)
    {
        parent::__construct($dtoFactory->get(Dto::class, $timer), $status);
    }
}
