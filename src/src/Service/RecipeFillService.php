<?php

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\RecipePosition;
use App\Entity\RecipePositionGroup;
use App\Entity\Tag;
use App\Entity\Timer;
use App\Entity\User;
use App\Model\Recipe as RecipeModel;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class RecipeFillService
{
    private EntityManagerInterface $entityManager;
    private TagRepository $tagRepository;
    private User $user;
    private Recipe $recipe;
    private RecipeModel $data;

    public function __construct(
        EntityManagerInterface $entityManager,
        TagRepository $tagRepository,
        UserService $userService
    ) {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
        $this->user = $userService->getUser();
    }

    public function setData(RecipeModel $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function fillRecipeBasicData(): static
    {
        $this->recipe->setName($this->data->getName());
        $this->recipe->setFavourite($this->data->isFavourite());
        $this->recipe->setToDo($this->data->isToDo());
        $this->recipe->setPublic($this->data->isPublic());
        $this->recipe->setDescription($this->data->getDescription());
        $this->recipe->setPortions($this->data->getPortions());
        $this->recipe->setUrl($this->data->getUrl());

        return $this;
    }

    public function assignTags(): static
    {
        foreach ($this->data->getTags() as $tag) {
            $tagEntity = $this->tagRepository->findOneBy([
                'name' => $tag,
                'user' => $this->user
            ]);
            if ($tagEntity === null) {
                $tagEntity = new Tag();
                $tagEntity->setName($tag);
                $tagEntity->setUser($this->user);
                $this->entityManager->persist($tagEntity);
            }
            $this->recipe->addTag($tagEntity);
        }

        return $this;
    }

    public function assignTimers(): static
    {
        foreach ($this->data->getTimers() as $timer) {
            $recipeTimer = new Timer();
            $recipeTimer->setUser($this->user);
            $recipeTimer->setName($timer->getName());
            $recipeTimer->setTime($timer->getTime());
            $this->entityManager->persist($recipeTimer);
            $this->recipe->addTimer($recipeTimer);
        }

        return $this;
    }

    public function assignPositions(): static
    {
        foreach ($this->data->getGroups() as $group) {
            $recipePositionsGroup = new RecipePositionGroup();
            $recipePositionsGroup->setName($group->getName());
            foreach ($group->getPositions() as $position) {
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
            $this->recipe->addRecipePositionGroup($recipePositionsGroup);
        }

        return $this;
    }

    public function setRecipe(Recipe $recipe): static
    {
        $this->recipe = $recipe;

        return $this;
    }
}
