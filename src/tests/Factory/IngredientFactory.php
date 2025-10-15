<?php

namespace App\Tests\Factory;

use App\Entity\Ingredient;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Ingredient>
 */
final class IngredientFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Ingredient::class;
    }

    protected function defaults(): array
    {
        return [
            'available' => self::faker()->boolean(),
            'name' => self::faker()->text(100),
            'user' => UserFactory::new(),
        ];
    }
}
