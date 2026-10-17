<?php

namespace App\Tests\DataProvider;

use App\Tests\Helper\EntityFactory;

class SimpleEntityDataProvider
{
    public static function apiKeysValues(): array
    {
        return [
            'lista z 1 ApiKey' => [[EntityFactory::getSimpleApiKey()]],
            'lista z 2 ApiKey' => [[EntityFactory::getSimpleApiKey(2), EntityFactory::getSimpleApiKey(3)]],
            'pusta lista' => [[]],
        ];
    }

    public static function ingredientsValues(): array
    {
        return [
            'lista z 1 Ingredient' => [[EntityFactory::getSimpleIngredient()]],
            'lista z 2 Ingredient' => [
                [EntityFactory::getSimpleIngredient(2), EntityFactory::getSimpleIngredient(3)],
            ],
            'pusta lista' => [[]],
        ];
    }

    public static function ozaSuppliesValues(): array
    {
        return [
            'lista z 1 OzaSupply' => [[EntityFactory::getSimpleOzaSupply()]],
            'lista z 2 OzaSupply' => [[EntityFactory::getSimpleOzaSupply(), EntityFactory::getSimpleOzaSupply(2)]],
            'pusta lista' => [[]],
        ];
    }

    public static function recipesValues(): array
    {
        return [
            'lista z 1 Recipe' => [[EntityFactory::getSimpleRecipe()]],
            'lista z 2 Recipe' => [[EntityFactory::getSimpleRecipe(2), EntityFactory::getSimpleRecipe(3)]],
            'pusta lista' => [[]],
        ];
    }

    public static function seasonsValues(): array
    {
        return [
            'lista z 1 Season' => [[EntityFactory::getSimpleSeason()]],
            'lista z 2 Season' => [[EntityFactory::getSimpleSeason(2), EntityFactory::getSimpleSeason(3)]],
            'pusta lista' => [[]],
        ];
    }

    public static function tagsValues(): array
    {
        return [
            'lista z 1 Tag' => [[EntityFactory::getSimpleTag()]],
            'lista z 2 Tag' => [[EntityFactory::getSimpleTag(2), EntityFactory::getSimpleTag(3)]],
            'pusta lista' => [[]],
        ];
    }

    public static function timersValues(): array
    {
        return [
            'lista z 1 Timer' => [[EntityFactory::getSimpleTimer()]],
            'lista z 2 Timer' => [[EntityFactory::getSimpleTimer(2), EntityFactory::getSimpleTimer(3)]],
            'pusta lista' => [[]],
        ];
    }
}
