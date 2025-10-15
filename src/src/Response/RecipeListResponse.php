<?php

namespace App\Response;

use App\Dto\Entity\List\RecipeList;
use App\Entity\Recipe;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class RecipeListResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Recipe ...$recipes)
    {
        parent::__construct($dtoFactory->getMany(RecipeList::class, ...$recipes));
    }
}
