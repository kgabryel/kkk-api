<?php

namespace App\Tests\Factory;

use App\Entity\ApiKey;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ApiKey>
 */
final class ApiKeyFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return ApiKey::class;
    }

    protected function defaults(): array
    {
        return [
            'active' => self::faker()->boolean(),
            'key' => self::faker()->regexify('[A-Za-z]{128}'),
            'user' => UserFactory::new(),
        ];
    }
}
