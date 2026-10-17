<?php

namespace App\Tests\Helper\Recipe;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipePositionGroup;
use App\Entity\User;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\EntityTestDataDto;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;

class RecipeTestResponseBuilder
{
    private array $createdIngredients;
    private array $createdRecipes;

    public function __construct()
    {
        $this->createdRecipes = [];
        $this->createdIngredients = [];
    }

    public function build(EntityTestDataDto $recipeData): RecipeResponse
    {
        $recipe = EntityFactory::createRecipe($recipeData->getUserEmail(), $recipeData->getEntityData());
        $this->createdRecipes[$recipe->getName()] = $recipe;
        $entityData = $recipeData->getEntityData();
        $photos = $this->preparePhotos($recipe, $recipeData->getParameter('photos') ?? []);

        return new RecipeResponse(
            $entityData,
            $recipe,
            $this->prepareGroups($recipe, $recipeData->getParameter('groups') ?? []),
            $this->sortPhotos($photos),
            $this->prepareTags($recipe, $recipeData->getParameter('tags') ?? []),
            $this->prepareTimers($recipe, $recipeData->getParameter('timers') ?? []),
        );
    }

    private function prepareGroups(Recipe $recipe, array $groups): array
    {
        $expectedGroups = [];
        foreach ($groups as $group) {
            $groupEntity = EntityFactory::createRecipePositionsGroup($recipe, ['name' => $group['name'] ?? '']);
            $positions = [];
            foreach ($group['positions'] ?? [] as $position) {
                $positions[] = $this->preparePositions($recipe->getUser(), $groupEntity, $position);
            }
            $expectedGroups[] = [
                'name' => $group['name'] ?? '',
                'positions' => $positions,
            ];
        }

        return $expectedGroups;
    }

    private function preparePhotos(Recipe $recipe, array $photos): array
    {
        foreach ($photos as $index => $photo) {
            $photoEntity = BaseFunctionalTestCase::createPhoto($recipe->getUser()->getEmail(), $photo);
            $recipe->addPhoto($photoEntity);
            $photos[$index]['id'] = $photoEntity->getId();
        }

        return $photos;
    }

    private function preparePositions(User $user, RecipePositionGroup $positionGroup, array $positionData): array
    {
        $ingredient = null;
        $recipe = null;
        if (($positionData['ingredient'] ?? null) !== null) {
            $ingredient = $this->provideIngredient($user, $positionData['ingredient']);
            $positionData['ingredient'] = $ingredient;
        }
        if (($positionData['recipe'] ?? null) !== null) {
            $recipe = $this->createdRecipes[$positionData['recipe']];
            $positionData['recipe'] = $recipe;
        }
        EntityFactory::createRecipePosition($positionGroup, $positionData);
        $positionData['ingredientId'] = $ingredient?->getId();
        $positionData['recipeId'] = $recipe?->getId();

        return $positionData;
    }

    private function prepareTags(Recipe $recipe, array $tags): array
    {
        $expectedTags = [];
        foreach ($tags as $tag) {
            $tagEntity = EntityFactory::createTag($recipe->getUser()->getEmail(), ['name' => $tag]);
            $expectedTags[] = [
                'id' => $tagEntity->getId(),
                'name' => $tag,
            ];
            $recipe->addTag($tagEntity);
        }

        return $expectedTags;
    }

    private function prepareTimers(Recipe $recipe, array $timers): array
    {
        $expectedTimers = [];
        foreach ($timers as $index => $timer) {
            $timerEntity = EntityFactory::createTimer($recipe->getUser()->getEmail(), $timer);
            $recipe->addTimer($timerEntity);
            $expectedTimers[$index] = [
                'id' => $timerEntity->getId(),
                'name' => $timer['name'] ?? null,
                'time' => $timer['time'],
            ];
        }

        return $expectedTimers;
    }

    private function provideIngredient(User $user, string $name): Ingredient
    {
        if (($this->createdIngredients[$name] ?? null) === null) {
            $this->createdIngredients[$name] = EntityFactory::createIngredient($user->getEmail(), ['name' => $name]);
        }

        return $this->createdIngredients[$name];
    }

    private function sortPhotos(array $photos): array
    {
        $tmp = [];
        foreach ($photos as $photo) {
            $tmp[] = [
                'height' => $photo['height'],
                'id' => $photo['id'],
                'type' => $photo['type'],
                'width' => $photo['width'],
            ];
        }

        return $tmp;
    }
}
