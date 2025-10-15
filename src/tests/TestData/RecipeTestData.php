<?php

namespace App\Tests\TestData;

use App\Entity\Ingredient;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Entity\Tag;

class RecipeTestData
{
    public static function expectedStoreResponse(
        Ingredient $ingredient1,
        Recipe $recipe1,
        Recipe $createdRecipe,
        array $tags
    ): array {
        return [
            'description' => 'description',
            'favourite' => false,
            'groups' => [
                [
                    'name' => '',
                    'positions' => [
                        [
                            'additional' => false,
                            'amount' => 1,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                            'recipe' => null,
                        ],
                        [
                            'additional' => true,
                            'amount' => null,
                            'ingredient' => null,
                            'measure' => 'kg',
                            'recipe' => $recipe1->getId(),
                        ],
                        [
                            'additional' => false,
                            'amount' => 3,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                            'recipe' => null,
                        ],
                    ],
                ],
                [
                    'name' => 'group-name',
                    'positions' => [
                        [
                            'additional' => true,
                            'amount' => 3.3,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'szt',
                            'recipe' => null,
                        ],
                    ],
                ],
            ],
            'id' => $createdRecipe->getId(),
            'name' => 'name',
            'photos' => [],
            'portions' => 3,
            'public' => true,
            'publicId' => $createdRecipe->getPublicId(),
            'tags' => array_map(static fn (Tag $tag): int => $tag->getId(), $tags),
            'timers' => [
                [
                    'id' => $createdRecipe->getTimers()[0]->getId(),
                    'name' => null,
                    'time' => 100,
                ],
                [
                    'id' => $createdRecipe->getTimers()[1]->getId(),
                    'name' => 'timer-name',
                    'time' => 200,
                ],
            ],
            'toDo' => false,
            'url' => null,
        ];
    }

    public static function expectedUpdateResponse(
        Ingredient $ingredient1,
        Recipe $recipe1,
        Recipe $recipe,
        array $photos,
        array $tags,
    ): array {
        return [
            'description' => 'description',
            'favourite' => false,
            'groups' => [
                [
                    'name' => '',
                    'positions' => [
                        [
                            'additional' => false,
                            'amount' => 1,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                            'recipe' => null,
                        ],
                        [
                            'additional' => true,
                            'amount' => null,
                            'ingredient' => null,
                            'measure' => 'kg',
                            'recipe' => $recipe1->getId(),
                        ],
                        [
                            'additional' => false,
                            'amount' => 3,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                            'recipe' => null,
                        ],
                    ],
                ],
                [
                    'name' => 'group-name',
                    'positions' => [
                        [
                            'additional' => true,
                            'amount' => 3.3,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'szt',
                            'recipe' => null,
                        ],
                    ],
                ],
            ],
            'id' => $recipe->getId(),
            'name' => 'name',
            'photos' => array_map(static fn (Photo $photo): array => [
                'height' => $photo->getHeight(),
                'id' => $photo->getId(),
                'type' => $photo->getType(),
                'width' => $photo->getWidth(),
            ], $photos),
            'portions' => 3,
            'public' => true,
            'publicId' => $recipe->getPublicId(),
            'tags' => array_map(static fn (Tag $tag): int => $tag->getId(), $tags),
            'timers' => [
                [
                    'id' => $recipe->getTimers()[0]->getId(),
                    'name' => null,
                    'time' => 100,
                ],
                [
                    'id' => $recipe->getTimers()[1]->getId(),
                    'name' => 'timer-name',
                    'time' => 200,
                ],
            ],
            'toDo' => false,
            'url' => null,
        ];
    }

    public static function validStoreRequest(Ingredient $ingredient1, Recipe $recipe1): array
    {
        return [
            'description' => 'description',
            'favourite' => false,
            'groups' => [
                [
                    'positions' => [
                        [
                            'additional' => false,
                            'amount' => 1.0,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                        ],
                        [
                            'additional' => true,
                            'measure' => 'kg',
                            'recipe' => $recipe1->getId(),
                        ],
                        [
                            'additional' => false,
                            'amount' => 3.0,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                        ],
                    ],
                ],
                [
                    'name' => 'group-name',
                    'positions' => [
                        [
                            'additional' => true,
                            'amount' => 3.3,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'szt',
                        ],
                    ],
                ],
            ],
            'name' => 'name',
            'portions' => 3,
            'public' => true,
            'tags' => ['TAG1', 'TAG2'],
            'timers' => [
                [
                    'time' => 100,
                ],
                [
                    'name' => 'timer-name',
                    'time' => 200,
                ],
            ],
            'toDo' => false,
            'url' => null,
        ];
    }

    public static function validUpdateRequest(Ingredient $ingredient1, Recipe $recipe1): array
    {
        return [
            'description' => 'description',
            'favourite' => false,
            'groups' => [
                [
                    'positions' => [
                        [
                            'additional' => false,
                            'amount' => 1.0,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                        ],
                        [
                            'additional' => true,
                            'measure' => 'kg',
                            'recipe' => $recipe1->getId(),
                        ],
                        [
                            'additional' => false,
                            'amount' => 3.0,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'kg',
                        ],
                    ],
                ],
                [
                    'name' => 'group-name',
                    'positions' => [
                        [
                            'additional' => true,
                            'amount' => 3.3,
                            'ingredient' => $ingredient1->getId(),
                            'measure' => 'szt',
                        ],
                    ],
                ],
            ],
            'name' => 'name',
            'portions' => 3,
            'public' => true,
            'tags' => ['TAG1', 'TAG2'],
            'timers' => [
                [
                    'time' => 100,
                ],
                [
                    'name' => 'timer-name',
                    'time' => 200,
                ],
            ],
            'toDo' => false,
            'url' => null,
        ];
    }
}
