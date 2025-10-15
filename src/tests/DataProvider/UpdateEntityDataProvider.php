<?php

namespace App\Tests\DataProvider;

class UpdateEntityDataProvider
{
    public static function ingredientValues(): array
    {
        return [
            'pełny zestaw: nazwa + available + ozaId' => [
                'available' => true,
                'expectedAvailable' => true,
                'expectedName' => 'Full',
                'expectedOzaId' => null,
                'name' => 'Full',
                'ozaId' => 456,
            ],
            'ustawia available = false i ignoruje ozaId' => [
                'available' => false,
                'expectedAvailable' => false,
                'expectedName' => null,
                'expectedOzaId' => null,
                'name' => null,
                'ozaId' => 123,
            ],
            'ustawia available = true' => [
                'available' => true,
                'expectedAvailable' => true,
                'expectedName' => null,
                'expectedOzaId' => null,
                'name' => null,
                'ozaId' => null,
            ],
            'ustawia tylko nazwę' => [
                'available' => null,
                'expectedAvailable' => null,
                'expectedName' => 'Nowa nazwa',
                'expectedOzaId' => null,
                'name' => 'Nowa nazwa',
                'ozaId' => null,
            ],
            'ustawia tylko ozaId (999)' => [
                'available' => null,
                'expectedAvailable' => null,
                'expectedName' => null,
                'expectedOzaId' => 999,
                'name' => null,
                'ozaId' => 999,
            ],
            'zeruje ozaId (0 -> null)' => [
                'available' => null,
                'expectedAvailable' => null,
                'expectedName' => null,
                'expectedOzaId' => null,
                'name' => null,
                'ozaId' => 0,
            ],
        ];
    }

    public static function photoAccessValues(): array
    {
        return [
            'różne ID użytkowników -> brak dostępu' => [
                'checkingUserId' => 2, // id użytkownika próbującego uzyskać dostęp do zdjęcia
                'connectedUserId' => 1, // id użytkownika posiadającego zdjęcie
                'result' => false, // rezultat sprawdzajania dostępu
            ],
            'użytkownik != właściciel -> brak dostępu' => [
                'checkingUserId' => null, // id użytkownika próbującego uzyskać dostęp do zdjęcia
                'connectedUserId' => 1, // id użytkownika posiadającego zdjęcie
                'result' => false, // rezultat sprawdzajania dostępu
            ],
            'użytkownik = właściciel -> dostęp' => [
                'checkingUserId' => 3, // id użytkownika próbującego uzyskać dostęp do zdjęcia
                'connectedUserId' => 3, // id użytkownika posiadającego zdjęcie
                'result' => true, // rezultat sprawdzajania dostępu
            ],
        ];
    }

    public static function seasonValues(): array
    {
        return [
            'zakres 1–2' => [
                'start' => 1,
                'stop' => 2,
            ],
            'zakres 3–12' => [
                'start' => 3,
                'stop' => 12,
            ],
        ];
    }

    public static function tagValues(): array
    {
        return [
            'nazwa wielkimi literami' => ['TAG VALUE'],
            'prosta nazwa tagu' => ['tag'],
        ];
    }

    public static function timerValues(): array
    {
        return [
            'nazwa + krótki czas' => [
                'name' => 'timer name',
                'time' => 1,
            ],
            'nazwa bez czasu' => [
                'name' => 'name',
                'time' => 0,
            ],
            'pusta nazwa + długi czas' => [
                'name' => '',
                'time' => 100,
            ],
        ];
    }
}
