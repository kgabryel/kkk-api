<?php

namespace App\Response;

use App\Dto\Entity\FullRecipe;
use App\Entity\Recipe;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class FullRecipeResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Recipe $recipe)
    {
        parent::__construct($dtoFactory->get(FullRecipe::class, $recipe));
    }
}
