<?php

namespace App\Tests\Factory;

use App\Entity\Settings;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Settings>
 */
final class SettingsFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Settings::class;
    }

    protected function defaults(): array
    {
        return [
            'autocomplete' => self::faker()->boolean(),
            'user' => UserFactory::new(),
        ];
    }
}
