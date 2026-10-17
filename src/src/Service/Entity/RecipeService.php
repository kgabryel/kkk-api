<?php

namespace App\Service\Entity;

use App\Dto\Request\Order;
use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use App\Service\RecipeFillService;
use App\Service\UserService;
use App\Validation\Recipe\RecipeValidation;
use App\Validation\RecipeFlagsValidation;
use App\Validation\ReorderPhotosValidation;
use Doctrine\ORM\EntityManagerInterface;

class RecipeService extends EntityService
{
    private RecipeFillService $recipeFillService;
    private RecipeRepository $recipeRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        RecipeRepository $recipeRepository,
        RecipeFillService $recipeFillService,
    ) {
        parent::__construct($entityManager, $userService);
        $this->recipeRepository = $recipeRepository;
        $this->recipeFillService = $recipeFillService;
    }

    public function find(int $id): ?Recipe
    {
        return $this->recipeRepository->findById($id, $this->user);
    }

    public function modify(Recipe $recipe, RecipeFlagsValidation $recipeFlagsValidation): bool
    {
        if (!$recipeFlagsValidation->validate()->passed()) {
            return false;
        }

        $data = $recipeFlagsValidation->getDto();
        $favourite = $data->getFavourite();
        $toDo = $data->getToDo();

        if ($favourite !== null) {
            $recipe->setFavourite($favourite);
        }
        if ($toDo !== null) {
            $recipe->setToDo($toDo);
        }

        $this->saveEntity($recipe);

        return true;
    }

    public function remove(Recipe $recipe, PhotoService $photoService): void
    {
        foreach ($recipe->getPhotos() as $photo) {
            $photoService->remove($photo);
        }
        $this->removeEntity($recipe);
    }

    public function reorderPhotos(Recipe $recipe, ReorderPhotosValidation $reorderPhotosValidation): bool
    {
        if (!$reorderPhotosValidation->validate($recipe)->passed()) {
            return false;
        }

        $order = $reorderPhotosValidation->getDto();
        $photosOrder = [];

        /** @var Order $item */
        foreach ($order->get() as $item) {
            $photosOrder[$item->getId()] = $item->getIndex();
        }
        foreach ($recipe->getPhotos() as $photo) {
            $photo->setPhotoOrder($photosOrder[$photo->getId()]);
            $this->saveEntity($photo);
        }

        return true;
    }

    public function update(Recipe $recipe, RecipeValidation $recipeValidation): bool
    {
        if (!$recipeValidation->validate()->passed()) {
            return false;
        }

        $data = $recipeValidation->getDto();
        $this->recipeFillService->fillRecipeBasicData($recipe, $data);
        $recipe->clearTags();
        $this->saveEntity($recipe);
        $this->recipeFillService->assignTags($recipe, $data);
        foreach ($recipe->getRecipePositionGroups() as $group) {
            $this->removeEntity($group);
        }
        foreach ($recipe->getTimers() as $timer) {
            $this->removeEntity($timer);
        }
        $this->recipeFillService->assignPositions($recipe, $data);
        $this->recipeFillService->assignTimers($recipe, $data);
        $this->entityManager->flush();

        return true;
    }
}
