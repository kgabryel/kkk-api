<?php

namespace App\Service;

use App\Dto\Request\Recipe as RecipeModel;
use App\Entity\Recipe;
use App\Entity\RecipePosition;
use App\Entity\RecipePositionGroup;
use App\Entity\Tag;
use App\Entity\Timer;
use App\Entity\User;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecipeFillService
{
    private EntityManagerInterface $entityManager;
    private TagRepository $tagRepository;
    private User $user;

    public function __construct(
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository,
        UserService $userService,
    ) {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->user = $userService->getUser();
    }

    public function assignPositions(Recipe $recipe, RecipeModel $data): self
    {
        foreach ($data->getGroups()->get() as $group) {
            $recipePositionsGroup = new RecipePositionGroup();
            $recipePositionsGroup->setName($group->getName());

            foreach ($group->getPositions()->get() as $position) {
                $positionEntity = new RecipePosition();
                $positionEntity->setIngredient($position->getIngredient());
                $positionEntity->setRecipe($position->getRecipe());
                $positionEntity->setAdditional($position->isAdditional());
                $positionEntity->setMeasure($position->getMeasure());
                $positionEntity->setAmount($position->getAmount());
                $recipePositionsGroup->addRecipePosition($positionEntity);
                $this->entityManager->persist($positionEntity);
            }

            $this->entityManager->persist($recipePositionsGroup);
            $recipe->addRecipePositionGroup($recipePositionsGroup);
        }

        return $this;
    }

    public function assignTags(Recipe $recipe, RecipeModel $data): self
    {
        foreach ($data->getTags()->get() as $tag) {
            $tagEntity = $this->tagRepository->findOneBy([
                'name' => $tag,
                'user' => $this->user,
            ]);
            if ($tagEntity === null) {
                $tagEntity = new Tag();
                $tagEntity->setName($tag);
                $tagEntity->setUser($this->user);
                $this->entityManager->persist($tagEntity);
            }
            $recipe->addTag($tagEntity);
        }

        return $this;
    }

    public function assignTimers(Recipe $recipe, RecipeModel $data): self
    {
        foreach ($data->getTimers()->get() as $timer) {
            $recipeTimer = new Timer();
            $recipeTimer->setUser($this->user);
            $recipeTimer->setName($timer->getName());
            $recipeTimer->setTime($timer->getTime());
            $this->entityManager->persist($recipeTimer);
            $recipe->addTimer($recipeTimer);
        }

        return $this;
    }

    public function fillRecipeBasicData(Recipe $recipe, RecipeModel $data): self
    {
        $recipe->setName($data->getName());
        $recipe->setFavourite($data->isFavourite());
        $recipe->setToDo($data->isToDo());
        $recipe->setPublic($data->isPublic());
        $recipe->setDescription($data->getDescription());
        $recipe->setPortions($data->getPortions());
        $recipe->setUrl($data->getUrl());

        return $this;
    }
}
