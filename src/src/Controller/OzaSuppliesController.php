<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Factory\DtoFactoryDispatcher;
use App\Repository\UserRepository;
use App\Service\Entity\IngredientService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OzaSuppliesController extends BaseController
{
    private IngredientService $ingredientService;

    public function __construct(
        DtoFactoryDispatcher $dtoFactory,
        UserRepository $userRepository,
        IngredientService $ingredientService,
    ) {
        parent::__construct($dtoFactory, $userRepository);
        $this->ingredientService = $ingredientService;
    }

    public function destroy(int $id): Response
    {
        $ingredient = $this->ingredientService->findByOzaId($id);
        if (!$ingredient instanceof Ingredient) {
            return $this->getNotFoundResponse();
        }

        $this->ingredientService->disconnectFromOZA($ingredient);

        return $this->getNoContentResponse();
    }

    public function modify(int $id, Request $request): Response
    {
        $ingredient = $this->ingredientService->findByOzaId($id);
        if (!$ingredient instanceof Ingredient) {
            return $this->getNotFoundResponse();
        }

        $available = (bool)$request->get('available');
        $this->ingredientService->updateAvailable($ingredient, $available);

        return $this->getNoContentResponse();
    }
}
