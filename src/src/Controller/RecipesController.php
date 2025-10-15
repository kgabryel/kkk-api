<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Factory\Entity\RecipeFactory;
use App\Repository\RecipeRepository;
use App\Response\FullRecipeResponse;
use App\Response\RecipeListResponse;
use App\Response\RecipeResponse;
use App\Service\Entity\PhotoService;
use App\Service\Entity\RecipeService;
use App\Validation\Recipe\RecipeValidation;
use App\Validation\RecipeFlagsValidation;
use Symfony\Component\HttpFoundation\Response;

class RecipesController extends BaseController
{
    public function destroy(int $id, RecipeService $recipeService, PhotoService $photoService): Response
    {
        $recipe = $recipeService->find($id);
        if (!($recipe instanceof Recipe)) {
            return $this->getNotFoundResponse();
        }

        $recipeService->remove($recipe, $photoService);

        return $this->getNoContentResponse();
    }

    public function index(RecipeRepository $recipeRepository): RecipeListResponse
    {
        return new RecipeListResponse(
            $this->dtoFactoryDispatcher,
            ...$recipeRepository->findForUser($this->getUser()),
        );
    }

    public function modify(
        int $id,
        RecipeFlagsValidation $recipeFlagsValidation,
        RecipeService $recipeService,
    ): Response {
        $recipe = $recipeService->find($id);
        if (!($recipe instanceof Recipe)) {
            return $this->getNotFoundResponse();
        }

        if (!$recipeService->modify($recipe, $recipeFlagsValidation)) {
            return $this->getBadRequestResponse();
        }

        return new RecipeResponse($this->dtoFactoryDispatcher, $recipe, Response::HTTP_OK);
    }

    public function public(string $id, RecipeRepository $recipeRepository): Response
    {
        $recipe = $recipeRepository->findOneBy([
            'public' => true,
            'publicId' => $id,
        ]);
        if (!($recipe instanceof Recipe)) {
            return $this->getNotFoundResponse();
        }

        return new FullRecipeResponse($this->dtoFactoryDispatcher, $recipe);
    }

    public function store(RecipeFactory $recipeFactory, RecipeValidation $recipeValidation): Response
    {
        $recipe = $recipeFactory->create($recipeValidation);
        if (!($recipe instanceof Recipe)) {
            return $this->getBadRequestResponse();
        }

        return new RecipeResponse($this->dtoFactoryDispatcher, $recipe, Response::HTTP_CREATED);
    }

    public function update(int $id, RecipeValidation $recipeValidation, RecipeService $recipeService): Response
    {
        $recipe = $recipeService->find($id);
        if (!($recipe instanceof Recipe)) {
            return $this->getNotFoundResponse();
        }

        if (!$recipeService->update($recipe, $recipeValidation)) {
            return $this->getBadRequestResponse();
        }

        return new RecipeResponse($this->dtoFactoryDispatcher, $recipe, Response::HTTP_OK);
    }
}
