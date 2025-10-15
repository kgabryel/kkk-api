<?php

namespace App\Tests\Factory;

use App\Entity\Season;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Season>
 */
final class SeasonFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Season::class;
    }

    protected function defaults(): array
    {
        return [
            'ingredient' => IngredientFactory::new(),
            'start' => self::faker()->randomNumber(),
            'stop' => self::faker()->randomNumber(),
            'user' => UserFactory::new(),
        ];
    }
}
