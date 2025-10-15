<?php

namespace App\Tests\Factory;

use App\Entity\RecipePosition;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<RecipePosition>
 */
final class RecipePositionFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return RecipePosition::class;
    }

    protected function defaults(): array
    {
        return [
            'additional' => self::faker()->boolean(),
            'measure' => self::faker()->text(100),
            'recipePositionGroup' => RecipePositionGroupFactory::new(),
        ];
    }
}
