<?php

namespace App\Tests\Factory;

use App\Entity\RecipePositionGroup;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<RecipePositionGroup>
 */
final class RecipePositionGroupFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return RecipePositionGroup::class;
    }

    protected function defaults(): array
    {
        return [
            'name' => self::faker()->text(255),
            'recipe' => RecipeFactory::new(),
        ];
    }
}
