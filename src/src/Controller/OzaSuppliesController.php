<?php

namespace App\Controller;

use App\Service\Entity\IngredientService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OzaSuppliesController extends BaseController
{
    private IngredientService $ingredientService;

    public function __construct(IngredientService $ingredientService)
    {
        $this->ingredientService = $ingredientService;
    }

    public function destroy(int $id): Response
    {
        if (!$this->ingredientService->findByOzaId($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $this->ingredientService->disconnectFromOZA();

        return new Response(null, Response::HTTP_OK);
    }

    public function modify(int $id, Request $request): Response
    {
        if (!$this->ingredientService->findByOzaId($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $available = (bool)$request->get('available');
        $this->ingredientService->updateAvailable($available);

        return new Response(null, Response::HTTP_OK);
    }
}
