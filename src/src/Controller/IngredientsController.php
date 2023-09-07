<?php

namespace App\Controller;

use App\Dto\Ingredient;
use App\Dto\OzaSupply;
use App\Factory\Entity\IngredientFactory;
use App\Form\IngredientForm;
use App\Repository\IngredientRepository;
use App\Service\Entity\IngredientService;
use App\Service\OzaSuppliesService;
use App\Service\SerializeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IngredientsController extends BaseController
{
    private SerializeService $serializer;

    public function __construct()
    {
        $this->serializer = SerializeService::getInstance(Ingredient::class);
    }

    public function index(IngredientRepository $ingredientRepository): Response
    {
        return new Response($this->serializer->serializeArray($ingredientRepository->findForUser($this->getUser())));
    }

    public function store(IngredientFactory $ingredientFactory, Request $request): Response
    {
        $form = $this->createForm(IngredientForm::class);
        $ingredient = $ingredientFactory->create($form, $request);
        if ($ingredient === null) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($ingredient));
    }

    public function modify(int $id, Request $request, IngredientService $ingredientService): Response
    {
        if (!$ingredientService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(IngredientForm::class, null, [
            self::EXPECT => $id,
            self::METHOD => Request::METHOD_PATCH
        ]);
        if (!$ingredientService->update($form, $request)) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($ingredientService->getIngredient()), Response::HTTP_OK);
    }

    public function destroy(int $id, IngredientService $ingredientService): Response
    {
        if (!$ingredientService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $ingredientService->remove();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function geOzaSupplies(OzaSuppliesService $ozaSuppliesService): Response
    {
        if (!$ozaSuppliesService->downloadSupplies()) {
            return new Response(null, $ozaSuppliesService->getErrorStatusCode());
        }
        $serializer = SerializeService::getInstance(OzaSupply::class);

        return new Response($serializer->serializeArray($ozaSuppliesService->getSupplies()));
    }
}
