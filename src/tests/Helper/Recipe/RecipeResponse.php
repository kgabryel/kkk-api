<?php

namespace App\Tests\Helper\Recipe;

use App\Entity\Recipe;

class RecipeResponse
{
    private array $entityData;
    private array $photos;
    private Recipe $recipe;
    private array $recipeGroups;
    private array $tags;
    private array $timers;

    public function __construct(
        array $entityData,
        Recipe $recipe,
        array $recipeGroups,
        array $photos,
        array $tags,
        array $timers
    ) {
        $this->entityData = $entityData;
        $this->recipe = $recipe;
        $this->recipeGroups = $recipeGroups;
        $this->photos = $photos;
        $this->tags = $tags;
        $this->timers = $timers;
    }

    public function asFullResponse(): array
    {
        return [
            'description' => $this->entityData['description'] ?? null,
            'favourite' => $this->entityData['favourite'],
            'groups' => $this->prepareGroups($this->recipeGroups),
            'id' => $this->recipe->getId(),
            'name' => $this->entityData['name'],
            'photos' => $this->photos,
            'portions' => $this->entityData['portions'],
            'public' => $this->entityData['public'],
            'publicId' => $this->recipe->getPublicId(),
            'tags' => array_map(static fn (array $tag) => $tag['id'], $this->tags),
            'timers' => $this->timers,
            'toDo' => $this->entityData['toDo'],
            'url' => $this->entityData['url'] ?? null,
        ];
    }

    public function asPublicResponse(): array
    {
        return [
            'description' => $this->entityData['description'] ?? null,
            'groups' => $this->preparePublicGroups($this->recipeGroups),
            'id' => $this->recipe->getId(),
            'name' => $this->entityData['name'],
            'photos' => $this->photos,
            'portions' => $this->entityData['portions'],
            'tags' => array_map(static fn (array $tag) => $tag['name'], $this->tags),
            'url' => $this->entityData['url'] ?? null,
        ];
    }

    public function getPublicId(): string
    {
        return $this->recipe->getPublicId();
    }

    private function prepareGroups(array $groups): array
    {
        foreach ($groups as $index => $group) {
            $tmp = [];
            foreach ($group['positions'] as $position) {
                $tmp[] = [
                    'additional' => $position['additional'],
                    'amount' => $position['amount'],
                    'ingredient' => $position['ingredientId'] ?? null,
                    'measure' => $position['measure'],
                    'recipe' => $position['recipeId'] ?? null,
                ];
            }
            $groups[$index] = [
                'name' => $group['name'],
                'positions' => $tmp,
            ];
        }

        return $groups;
    }

    private function preparePublicGroups(array $groups): array
    {
        foreach ($groups as $index => $group) {
            $tmp = [];
            foreach ($group['positions'] as $position) {
                $tmp[] = [
                    'additional' => $position['additional'],
                    'amount' => $position['amount'],
                    'ingredient' => ($position['ingredient'] ?? null)?->getName()
                        ?? ($position['recipe'] ?? null)?->getName()
                            ?? null,
                    'measure' => $position['measure'],
                ];
            }
            $groups[$index] = [
                'name' => $group['name'],
                'positions' => $tmp,
            ];
        }

        return $groups;
    }
}
