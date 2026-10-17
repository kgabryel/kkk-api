<?php

namespace App\Tests\Factory;

use App\Entity\Photo;
use Ramsey\Uuid\Uuid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Photo>
 */
final class PhotoFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Photo::class;
    }

    protected function defaults(): array
    {
        return [
            'fileName' => Uuid::uuid4()->toString(),
            'height' => self::faker()->randomNumber(),
            'recipe' => RecipeFactory::new(),
            'type' => self::faker()->text(100),
            'user' => UserFactory::new(),
            'width' => self::faker()->randomNumber(),
        ];
    }
}
