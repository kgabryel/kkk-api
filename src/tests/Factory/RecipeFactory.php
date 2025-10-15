<?php

namespace App\Tests\Factory;

use App\Entity\Recipe;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Recipe>
 */
final class RecipeFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Recipe::class;
    }

    protected function defaults(): array
    {
        return [
            'description' => self::faker()->randomElement([null, self::faker()->text(100)]),
            'favourite' => self::faker()->boolean(),
            'name' => self::faker()->text(255),
            'portions' => self::faker()->numberBetween(0, 100),
            'public' => self::faker()->boolean(),
            'publicId' => self::faker()->text(128),
            'toDo' => self::faker()->boolean(),
            'url' => self::faker()->randomElement([null, self::faker()->url()]),
            'user' => UserFactory::new(),
        ];
    }
}
