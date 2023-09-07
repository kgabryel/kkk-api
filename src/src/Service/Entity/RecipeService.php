<?php

namespace App\Service\Entity;

use App\Entity\Recipe;
use App\Model\Recipe as RecipeModel;
use App\Repository\RecipeRepository;
use App\Service\RecipeFillService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecipeService extends EntityService
{
    private Recipe $recipe;
    private RecipeRepository $recipeRepository;
    private RecipeFillService $recipeFillService;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        RecipeRepository $recipeRepository,
        RecipeFillService $recipeFillService
    ) {
        parent::__construct($entityManager, $tokenStorage);
        $this->recipeRepository = $recipeRepository;
        $this->recipeFillService = $recipeFillService;
    }

    public function find(int $id): bool
    {
        $recipe = $this->recipeRepository->findById($id, $this->user);
        if ($recipe === null) {
            return false;
        }
        $this->recipe = $recipe;

        return true;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function modify(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        /** @var RecipeModel $data */
        $data = $form->getData();
        $favourite = $data->isFavourite();
        $toDo = $data->isToDo();
        if ($favourite !== null) {
            $this->recipe->setFavourite($favourite);
        }
        if ($toDo !== null) {
            $this->recipe->setToDo($toDo);
        }
        $this->saveEntity($this->recipe);

        return true;
    }

    public function update(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        /** @var RecipeModel $data */
        $data = $form->getData();
        $this->recipeFillService->setRecipe($this->recipe)->setData($data)->fillRecipeBasicData();
        $this->recipe->clearTags();
        $this->saveEntity($this->recipe);
        $this->recipeFillService->assignTags();
        foreach ($this->recipe->getRecipePositionGroups() as $group) {
            $this->removeEntity($group);
        }
        foreach ($this->recipe->getTimers() as $timer) {
            $this->removeEntity($timer);
        }
        $this->recipeFillService->assignPositions();
        $this->recipeFillService->assignTimers();
        $this->entityManager->flush();

        return true;
    }

    public function remove(PhotoService $photoService): void
    {
        foreach ($this->recipe->getPhotos() as $recipe) {
            $photoService->set($recipe)->remove();
        }
        $this->removeEntity($this->recipe);
    }

    public function reorderPhotos(Request $request): void
    {
        $order = $request->get('order') ?? [];
        if (!is_array($order)) {
            return;
        }
        $photosOrder = [];
        foreach ($order as $item) {
            $photosOrder[(int)($item['id'] ?? 0)] = (int)($item['index'] ?? 1);
        }
        foreach ($this->recipe->getPhotos() as $photo) {
            $photo->setPhotoOrder($photosOrder[$photo->getId()] ?? 1);
            $this->saveEntity($photo);
        }
    }
}
