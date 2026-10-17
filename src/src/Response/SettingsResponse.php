<?php

namespace App\Response;

use App\Dto\Entity\Settings as Dto;
use App\Entity\Settings;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class SettingsResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Settings $settings, int $status)
    {
        parent::__construct($dtoFactory->get(Dto::class, $settings), $status);
    }
}
