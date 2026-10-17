<?php

namespace App\Response;

use App\Dto\Entity\List\TimerList;
use App\Entity\Timer;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class TimerListResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Timer ...$timers)
    {
        parent::__construct($dtoFactory->getMany(TimerList::class, ...$timers));
    }
}
