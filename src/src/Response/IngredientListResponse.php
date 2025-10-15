<?php

namespace App\Response;

use App\Dto\Entity\List\IngredientList;
use App\Entity\Ingredient;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class IngredientListResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Ingredient ...$ingredients)
    {
        parent::__construct($dtoFactory->getMany(IngredientList::class, ...$ingredients));
    }
}
