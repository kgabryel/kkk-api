<?php

namespace App\Tests\DataProvider;

use App\Dto\Entity\ApiKey;
use App\Dto\Entity\FullRecipePosition;
use App\Dto\Entity\Ingredient;
use App\Dto\Entity\OzaSupply;
use App\Dto\Entity\Photo;
use App\Dto\Entity\RecipePosition;
use App\Dto\Entity\Season;
use App\Dto\Entity\Settings;
use App\Dto\Entity\Tag;
use App\Dto\Entity\Timer;

class DtoFactoryDataProvider
{
    public static function apiKeyValues(): array
    {
        return [
            'poprawny klucz aktywny' => [[1, 'key', true], new ApiKey(1, 'key', true)],
            'pusty klucz nieaktywny' => [[2, '', false], new ApiKey(2, '', false)],
        ];
    }

    public static function fullRecipePositionValues(): array
    {
        return [
            'ingredient, dodatkowe = true, amount = 0' => [
                [0.0, 'kg', true, 'ingredient name', null],
                new FullRecipePosition(
                    0.0,
                    'kg',
                    true,
                    'ingredient name',
                    null,
                ),
            ],
            'recipe, dodatkowe = true, amount = 2' => [
                [2.0, 'ml', true, null, 'recipe name'],
                new FullRecipePosition(
                    2.0,
                    'ml',
                    true,
                    null,
                    'recipe name',
                ),
            ],
        ];
    }

    public static function ingredientValues(): array
    {
        return [
            'pusty name, available = false, brak ozaId' => [
                [1, '', false, null],
                new Ingredient(1, '', false, null),
            ],
            'ustawiony name, available = true, z ozaId' => [
                [2, 'name', true, 1],
                new Ingredient(2, 'name', true, 1),
            ],
        ];
    }

    public static function ozaSupplyValues(): array
    {
        return [
            'supply null (brak id, group, unit, amount)' => [
                (object)[
                    'amount' => null,
                    'group' => null,
                    'id' => null,
                    'unit' => null,
                ],
                new OzaSupply(0, '', false, ''),
            ],
            'supply z amount = 0, group = Płyny, unit = l (niedostępne)' => [
                (object)[
                    'amount' => 0,
                    'group' => (object)['name' => 'Płyny'],
                    'id' => 2,
                    'unit' => (object)['shortcut' => 'l'],
                ],
                new OzaSupply(2, 'Płyny', false, '0l'),
            ],
            'supply z amount = 5, brak group i unit' => [
                (object)[
                    'amount' => 5,
                    'group' => null,
                    'id' => 3,
                    'unit' => null,
                ],
                new OzaSupply(3, '', true, '5'),
            ],
            'supply z amount=10, group = Warzywa, unit = kg' => [
                (object)[
                    'amount' => 10,
                    'group' => (object)['name' => 'Warzywa'],
                    'id' => 1,
                    'unit' => (object)['shortcut' => 'kg'],
                ],
                new OzaSupply(1, 'Warzywa', true, '10kg'),
            ],
        ];
    }

    public static function photoValues(): array
    {
        return [
            'photo id = 1, 1x1 px, png' => [[1, 1, 1, 'png'], new Photo(1, 1, 1, 'png')],
            'photo id = 2, 3x4 px, jpeg' => [[2, 3, 4, 'jpeg'], new Photo(2, 3, 4, 'jpeg')],
        ];
    }

    public static function recipePositionValues(): array
    {
        return [
            'ingredient = 1, recipe = null, additional = true' => [
                [2.0, 'ml', 1, null, true],
                new RecipePosition(
                    2.0,
                    'ml',
                    1,
                    null,
                    true,
                ),
            ],
            'ingredient = null, recipe = 3, additional false' => [
                [0.0, 'kg', null, 3, false],
                new RecipePosition(
                    0.0,
                    'kg',
                    null,
                    3,
                    false,
                ),
            ],
        ];
    }

    public static function seasonValues(): array
    {
        return [
            'id = 1, ingredientId = 1, start = 1, stop = 1' => [
                [1, 1, 1, 1],
                new Season(1, 1, 1, 1),
            ],
            'id = 2, ingredientId = 3, start = 4, stop = 5' => [
                [2, 3, 4, 5],
                new Season(2, 3, 4, 5),
            ],
        ];
    }

    public static function settingsValues(): array
    {
        return [
            'autocomplete = false, ozaKey = key, userType = facebook' => [
                [false, 'key', 'key'],
                new Settings(
                    false,
                    'key',
                    false,
                ),
            ],
            'autocomplete = true, brak ozaKey, userType = standard' => [
                [true, null, null],
                new Settings(true, null, true),
            ],
        ];
    }

    public static function tagValues(): array
    {
        return [
            'tag z id = 1 i name = "name"' => [[1, 'name'], new Tag(1, 'name')],
            'tag z id = 2 i name = "NAME2"' => [[2, 'NAME2'], new Tag(2, 'NAME2')],
        ];
    }

    public static function timerValues(): array
    {
        return [
            'timer id = 1, name = "name", time = 0' => [[1, 'name', 0], new Timer(1, 'name', 0)],
            'timer id = 2, pusty name, time = 1' => [[2, '', 1], new Timer(2, '', 1)],
        ];
    }
}
