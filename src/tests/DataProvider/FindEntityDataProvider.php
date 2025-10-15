<?php

namespace App\Tests\DataProvider;

use App\Tests\Helper\EntityFactory;

class FindEntityDataProvider
{
    public static function apiKeyValues(): array
    {
        return [
            'nie znaleziono ApiKey (null)' => [1, null],
            'znaleziono ApiKey (id = 2)' => [2, EntityFactory::getSimpleApiKey()],
        ];
    }

    public static function ingredientValues(): array
    {
        return [
            'nie znaleziono Ingredient (null)' => [1, null],
            'znaleziono Ingredient (id = 2)' => [2, EntityFactory::getSimpleIngredient()],
        ];
    }

    public static function photoValues(): array
    {
        return [
            'nie znaleziono Photo (null)' => [1, null],
            'znaleziono Photo (id = 2)' => [2, EntityFactory::getSimplePhoto()],
        ];
    }

    public static function recipeValues(): array
    {
        return [
            'nie znaleziono Recipe (null)' => [1, null],
            'znaleziono Recipe (id = 2)' => [2, EntityFactory::getSimpleRecipe()],
        ];
    }

    public static function seasonValues(): array
    {
        return [
            'nie znaleziono Season (null)' => [1, null],
            'znaleziono Season (id = 2)' => [2, EntityFactory::getSimpleSeason()],
        ];
    }

    public static function tagValues(): array
    {
        return [
            'nie znaleziono Tag (null)' => [1, null],
            'znaleziono Tag (id = 2)' => [2, EntityFactory::getSimpleTag()],
        ];
    }

    public static function timerValues(): array
    {
        return [
            'nie znaleziono Timer (null)' => [1, null],
            'znaleziono Timer (id = 2)' => [2, EntityFactory::getSimpleTimer()],
        ];
    }
}
