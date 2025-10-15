<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Factory\Entity\IngredientFactory;
use App\Repository\IngredientRepository;
use App\Response\IngredientListResponse;
use App\Response\IngredientResponse;
use App\Response\OzaSupplyListResponse;
use App\Service\Entity\IngredientService;
use App\Service\OzaSuppliesService;
use App\Validation\CreateIngredientValidation;
use App\Validation\EditIngredientValidation;
use Symfony\Component\HttpFoundation\Response;

class IngredientsController extends BaseController
{
    public function destroy(int $id, IngredientService $ingredientService): Response
    {
        $ingredient = $ingredientService->find($id);
        if (!$ingredient instanceof Ingredient) {
            return $this->getNotFoundResponse();
        }

        $ingredientService->remove($ingredient);

        return $this->getNoContentResponse();
    }

    public function getOzaSupplies(OzaSuppliesService $ozaSuppliesService): Response
    {
        if (!$ozaSuppliesService->downloadSupplies()) {
            return new Response(status: $ozaSuppliesService->getErrorStatusCode());
        }

        return new OzaSupplyListResponse($this->dtoFactoryDispatcher, ...$ozaSuppliesService->getSupplies());
    }

    public function index(IngredientRepository $ingredientRepository): IngredientListResponse
    {
        return new IngredientListResponse(
            $this->dtoFactoryDispatcher,
            ...$ingredientRepository->findForUser($this->getUser()),
        );
    }

    public function modify(
        int $id,
        EditIngredientValidation $ingredientValidation,
        IngredientService $ingredientService,
    ): Response {
        $ingredient = $ingredientService->find($id);
        if (!$ingredient instanceof Ingredient) {
            return $this->getNotFoundResponse();
        }

        if (!$ingredientService->update($ingredient, $ingredientValidation)) {
            return $this->getBadRequestResponse();
        }

        return new IngredientResponse($this->dtoFactoryDispatcher, $ingredient, Response::HTTP_OK);
    }

    public function store(
        IngredientFactory $ingredientFactory,
        CreateIngredientValidation $ingredientValidation,
    ): Response {
        $ingredient = $ingredientFactory->create($ingredientValidation);
        if (!($ingredient instanceof Ingredient)) {
            return $this->getBadRequestResponse();
        }

        return new IngredientResponse($this->dtoFactoryDispatcher, $ingredient, Response::HTTP_CREATED);
    }
}
