<?php

namespace App\Factory\Entity;

use App\Entity\Recipe;
use App\Service\RecipeFillService;
use App\Service\UserService;
use App\Utils\UuidGenerator;
use App\Validation\Recipe\RecipeValidation;
use Doctrine\ORM\EntityManagerInterface;

class RecipeFactory extends EntityFactory
{
    private RecipeFillService $recipeFillService;
    private UuidGenerator $uuidGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        RecipeFillService $recipeFillService,
        UuidGenerator $uuidGenerator,
    ) {
        parent::__construct($entityManager, $userService);
        $this->recipeFillService = $recipeFillService;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(RecipeValidation $recipeValidation): ?Recipe
    {
        if (!$recipeValidation->validate()->passed()) {
            return null;
        }

        $data = $recipeValidation->getDto();
        $recipe = new Recipe();
        $recipe->setUser($this->user);
        $recipe->setPublicId($this->uuidGenerator->generate());
        $this->recipeFillService->fillRecipeBasicData($recipe, $data);
        $this->saveEntity($recipe);
        $this->recipeFillService->assignTags($recipe, $data)
            ->assignPositions($recipe, $data)
            ->assignTimers($recipe, $data);
        $this->entityManager->flush();

        return $recipe;
    }
}
