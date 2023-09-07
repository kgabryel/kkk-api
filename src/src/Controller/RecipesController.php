<?php

namespace App\Controller;

use App\Dto\FullRecipe;
use App\Dto\Recipe;
use App\Factory\Entity\RecipeFactory;
use App\Form\RecipeForm;
use App\Repository\RecipeRepository;
use App\Service\Entity\PhotoService;
use App\Service\Entity\RecipeService;
use App\Service\SerializeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RecipesController extends BaseController
{
    private SerializeService $serializer;

    public function __construct()
    {
        $this->serializer = SerializeService::getInstance(Recipe::class);
    }

    public function index(RecipeRepository $recipeRepository): Response
    {
        return new Response($this->serializer->serializeArray($recipeRepository->findForUser($this->getUser())));
    }

    public function store(RecipeFactory $recipeFactory, Request $request): Response
    {
        $form = $this->createForm(RecipeForm::class);
        $recipe = $recipeFactory->create($form, $request);
        if ($recipe === null) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($recipe));
    }

    public function modify(int $id, Request $request, RecipeService $recipeService): Response
    {
        if (!$recipeService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(RecipeForm::class, null, [
            self::METHOD => Request::METHOD_PATCH
        ]);
        if (!$recipeService->modify($form, $request)) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($recipeService->getRecipe()), Response::HTTP_OK);
    }

    public function update(int $id, Request $request, RecipeService $recipeService): Response
    {
        if (!$recipeService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(RecipeForm::class, null, [
            self::METHOD => Request::METHOD_PUT
        ]);
        if (!$recipeService->update($form, $request)) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($recipeService->getRecipe()), Response::HTTP_OK);
    }

    public function destroy(int $id, RecipeService $recipeService, PhotoService $photoService): Response
    {
        if (!$recipeService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $recipeService->remove($photoService);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function public(string $id, RecipeRepository $recipeRepository): Response
    {
        $recipe = $recipeRepository->findOneBy([
            'public' => true,
            'publicId' => $id
        ]);
        if ($recipe === null) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }
        $serializer = SerializeService::getInstance(FullRecipe::class);

        return new Response($serializer->serialize($recipe), Response::HTTP_OK);
    }
}
