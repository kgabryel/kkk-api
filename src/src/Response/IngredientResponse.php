<?php

namespace App\Response;

use App\Dto\Entity\Ingredient as Dto;
use App\Entity\Ingredient;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class IngredientResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Ingredient $ingredient, int $status)
    {
        parent::__construct($dtoFactory->get(Dto::class, $ingredient), $status);
    }
}
