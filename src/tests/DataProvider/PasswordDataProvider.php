<?php

namespace App\Tests\DataProvider;

class PasswordDataProvider
{
    public static function emptyPasswordValues(): array
    {
        return [
            'brak "password"' => [['abs' => ['first' => 'abc']]],
            'brak pól "first" i "second" w "password"' => [['password' => []]],
        ];
    }
}
