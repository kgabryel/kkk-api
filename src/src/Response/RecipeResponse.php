<?php

namespace App\Response;

use App\Dto\Entity\Recipe as Dto;
use App\Entity\Recipe;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class RecipeResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Recipe $recipe, int $status)
    {
        parent::__construct($dtoFactory->get(Dto::class, $recipe), $status);
    }
}
