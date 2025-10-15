<?php

namespace App\Tests\Factory;

use App\Entity\Timer;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Timer>
 */
final class TimerFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Timer::class;
    }

    protected function defaults(): array
    {
        return [
            'time' => self::faker()->randomNumber(),
            'user' => UserFactory::new(),
        ];
    }
}
