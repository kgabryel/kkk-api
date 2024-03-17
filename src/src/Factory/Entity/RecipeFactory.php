<?php

namespace App\Factory\Entity;

use App\Entity\Recipe;
use App\Model\Recipe as RecipeModel;
use App\Service\RecipeFillService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class RecipeFactory extends EntityFactory
{
    private RecipeFillService $recipeFillService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        RecipeFillService $recipeFillService
    ) {
        parent::__construct($entityManager, $userService);
        $this->recipeFillService = $recipeFillService;
    }

    public function create(FormInterface $form, Request $request): ?Recipe
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }
        /** @var RecipeModel $data */
        $data = $form->getData();
        $recipe = new Recipe();
        $recipe->setUser($this->user);
        $recipe->setPublicId(Uuid::uuid4()->toString());
        $this->recipeFillService->setRecipe($recipe)->setData($data)->fillRecipeBasicData();
        $this->saveEntity($recipe);
        $this->recipeFillService->assignTags()->assignPositions()->assignTimers();
        $this->entityManager->flush();

        return $recipe;
    }
}
