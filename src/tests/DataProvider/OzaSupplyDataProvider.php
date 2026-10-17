<?php

namespace App\Tests\DataProvider;

class OzaSupplyDataProvider
{
    public static function errorStatusCodesValues(): array
    {
        return [
            '401 -> zwraca 403' => [
                'expectedCode' => 403, // status code zwrócony z API
                'statusCode' => 401, // status code zwrócony z API OZA
            ],
            '403 -> zwraca 403' => [
                'expectedCode' => 403, // status code zwrócony z API
                'statusCode' => 403, // status code zwrócony z API OZA
            ],
            '500 -> zwraca 500' => [
                'expectedCode' => 500, // status code zwrócony z API
                'statusCode' => 500, // status code zwrócony z API OZA
            ],
        ];
    }

    public static function ingredientsWithOzaIdValues(): array
    {
        return [
            'pierwszy id 1, dalej null i inny id 2 -> znajdzie 1' => [
                // oczekiwany id Oza Supply
                'expectedOzaId' => 1,
                // id Oza Supply przypisane do kolejnych składników
                'ozaIds' => [
                    1,
                    null,
                    2,
                ],
            ],
            'pierwszy null, drugi z id 123 -> znajdzie 123' => [
                // oczekiwany id Oza Supply
                'expectedOzaId' => 123,
                // id Oza Supply przypisane do kolejnych składników
                'ozaIds' => [
                    null,
                    123,
                ],
            ],
            'same null values -> brak składnika' => [
                // oczekiwany id Oza Supply
                'expectedOzaId' => null,
                // id Oza Supply przypisane do kolejnych składników
                'ozaIds' => [
                    null,
                    null,
                ],
            ],
        ];
    }

    public static function ozaIdValues(): array
    {
        return [
            'brak ozaId (null -> null)' => [
                'expectedId' => null,
                'requestId' => null,
            ],
            'ozaId = 0 traktowany jako null' => [
                'expectedId' => null,
                'requestId' => 0,
            ],
            'poprawny ozaId = 1' => [
                'expectedId' => 1,
                'requestId' => 1,
            ],
        ];
    }

    public static function responseValues(): array
    {
        return [
            'odpowiedź z jednym produktem' => [
                // surowa odpowiedź z API OZA
                '[{
                "id": 79,
                "description": null,
                "group": {
                    "id": 75,
                    "name": "Produkt 1"
                },
                "amount": 850,
                "unit": {
                    "id": 6,
                    "name": "Mililitr",
                    "shortcut": "ml"
                },
                "alertsLength": 0,
                "updatedAt": "2022-04-05 20:44",
                "groups": []
            }]',
                // przetworzone oczekiwane dane
                [
                    (object)[
                        'alertsLength' => 0,
                        'amount' => 850,
                        'description' => null,
                        'group' => (object)[
                            'id' => 75,
                            'name' => 'Produkt 1',
                        ],
                        'groups' => [],
                        'id' => 79,
                        'unit' => (object)[
                            'id' => 6,
                            'name' => 'Mililitr',
                            'shortcut' => 'ml',
                        ],
                        'updatedAt' => '2022-04-05 20:44',
                    ],
                ],
            ],
            'pusta odpowiedź API ({} -> [])' => [
                // surowa odpowiedź z API OZA
                '{}',
                // przetworzone oczekiwane dane
                [],
            ],
        ];
    }

    public static function statusesValues(): array
    {
        return [
            'available = false -> niedostępny' => [
                'expectedAvailable' => false,
                'requestValue' => false,
            ],
            'available = null -> niedostępny' => [
                'expectedAvailable' => false,
                'requestValue' => null,
            ],
            'available = pusty string -> niedostępny' => [
                'expectedAvailable' => false,
                'requestValue' => '',
            ],
            'available = true -> dostępny' => [
                'expectedAvailable' => true,
                'requestValue' =>  true,
            ],
        ];
    }
}
