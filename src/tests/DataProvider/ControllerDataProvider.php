<?php

namespace App\Tests\DataProvider;

use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\EntityTestDataDto;

class ControllerDataProvider
{
    public static function apiKeysData(): array
    {
        $user1EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL, ['active' => true]);
        $user2EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL_2);

        return [
            '2 klucze użytkownika i 1 innego -> 2 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['active' => false]),
                    $user2EntityData,
                ],
            ],
            '3 klucze użytkownika -> 3 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['active' => false]),
                    $user1EntityData->clone(['active' => false, 'key' => str_repeat('a', 128)]),
                ],
            ],
            '3 klucze użytkownika i 4 innego -> 3 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['active' => false]),
                    $user1EntityData->clone(['active' => false, 'key' => str_repeat('a', 128)]),
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
            'brak encji w bazie' => [[]],
            'same klucze innego użytkownika -> brak wyników' => [
                [
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
        ];
    }

    public static function createIngredientValidValues(): array
    {
        return [
            'prosty składnik z ozaId = 1' => [
                'available' => false,
                'name' => 'name',
                'ozaId' => 1,
            ],
            'składnik dostępny bez ozaId' => [
                'available' => true,
                'name' => 'ingredient name',
                'ozaId' => null,
            ],
            'składnik niedostępny z ozaId = 20' => [
                'available' => false,
                'name' => 'name2',
                'ozaId' => 20,
            ],
        ];
    }

    public static function ingredientIndexData(): array
    {
        $user1EntityData = new EntityTestDataDto(
            EntityFactory::USER_EMAIL,
            ['name' => 'ingredient1', 'available' => true, 'ozaId' => null],
        );
        $user2EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL_2);

        return [
            '2 składniki użytkownika i 1 innego -> 2 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['name' => 'ingredient2', 'available' => false]),
                    $user2EntityData,
                ],
            ],
            '3 składniki -> 2 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['name' => 'ingredient2', 'available' => false]),
                    $user1EntityData->clone(['name' => 'ingredient3']),
                ],
            ],
            '3 składniki użytkownika i 4 innego -> 3 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['name' => 'ingredient2', 'available' => false]),
                    $user1EntityData->clone(['name' => 'ingredient3']),
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
            'brak encji w bazie -> brak wyników' => [[]],
            'same składniki innego użytkownika -> brak wyników' => [
                [
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
        ];
    }

    public static function modifyRecipeValidValues(): array
    {
        return [
            'brak zmian (favourite = null, toDo = null)' => [
                'favourite' => null,
                'toDo' => null,
            ],
            'ustawia favourite i toDo na true' => [
                'favourite' => true,
                'toDo' => true,
            ],
        ];
    }

    public static function publicRecipeData(): array
    {
        $entityData = new EntityTestDataDto(
            'email@example.com',
            [
                'description' => null,
                'name' => 'recipe-name',
                'portions' => 1,
                'public' => true,
                'publicId' => 'public-id',
                'url' => null,
            ],
        );
        $entityData2 = $entityData->clone(['url' => 'url'], ['tags' => ['TAG1', 'TAG2']]);

        return [
            'prosty przepis publiczny, bez tagów' => [$entityData],
            'przepis publiczny z tagami i url' => [$entityData2],
            'przepis z inną nazwą, opisem i 1 tagiem' => [
                $entityData->clone(
                    ['name' => 'name2', 'description' => 'description', 'portions' => 2],
                    ['tags' => ['TAG1']],
                ),
            ],
            'przepis z jedną grupą (1 pozycja: składnik)' => [
                $entityData2->clone(parameters: [
                    'groups' => [
                        [
                            'name' => 'name',
                            'positions' => [
                                [
                                    'additional' => false,
                                    'amount' => 1,
                                    'ingredient' => 'ingredient-name',
                                    'measure' => 'ml',
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            'przepis z jedną grupą  (2 pozycje: składnik + przepis)' => [
                $entityData2->clone(parameters: [
                    'groups' => [
                        [
                            'name' => 'name',
                            'positions' => [
                                [
                                    'additional' => true,
                                    'amount' => 1,
                                    'ingredient' => 'ingredient-name',
                                    'measure' => 'ml',
                                ],
                                ['additional' => true, 'amount' => 2, 'recipe' => 'recipe-name', 'measure' => 'szt'],
                            ],
                        ],
                    ],
                ]),
            ],
            'przepis z jedną grupą i dwoma zdjęciami' => [
                $entityData2->clone(parameters: [
                    'groups' => [
                        [
                            'name' => 'name',
                            'positions' => [
                                [
                                    'additional' => true,
                                    'amount' => 1,
                                    'ingredient' => 'ingredient-name',
                                    'measure' => 'ml',
                                ],
                                ['additional' => true, 'amount' => 2, 'recipe' => 'recipe-name', 'measure' => 'szt'],
                            ],
                        ],
                    ],
                    'photos' => [
                        [
                            'height' => 500,
                            'type' => 'image/jpg',
                            'width' => 100,
                        ],
                        [
                            'height' => 200,
                            'type' => 'image/png',
                            'width' => 100,
                        ],
                    ],
                ]),
            ],
            'przepis z jedną grupą i zdjęciem' => [
                $entityData2->clone(parameters: [
                    'groups' => [
                        [
                            'name' => 'name',
                            'positions' => [
                                ['additional' => true, 'amount' => 2, 'recipe' => 'recipe-name', 'measure' => 'szt'],
                                [
                                    'additional' => true,
                                    'amount' => 1,
                                    'ingredient' => 'ingredient-name',
                                    'measure' => 'ml',
                                ],
                            ],
                        ],
                    ],
                    'photos' => [
                        [
                            'height' => 100,
                            'type' => 'image/jpg',
                            'width' => 100,
                        ],
                    ],
                ]),
            ],
            'przepis z wieloma grupami i pozycjami' => [
                $entityData2->clone(parameters: [
                    'groups' => [
                        [
                            'name' => 'name',
                            'positions' => [
                                [
                                    'additional' => true,
                                    'amount' => 1,
                                    'ingredient' => 'ingredient-name',
                                    'measure' => 'ml',
                                ],
                                [
                                    'additional' => true,
                                    'amount' => 2,
                                    'measure' => 'szt',
                                    'recipe' => 'recipe-name',
                                ],
                            ],
                        ],
                        [
                            'positions' => [
                                [
                                    'additional' => true,
                                    'amount' => 1,
                                    'ingredient' => 'ingredient-name',
                                    'measure' => 'ml',
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ];
    }

    public static function recipeIndexData(): array
    {
        $user1EntityData = new EntityTestDataDto(
            EntityFactory::USER_EMAIL,
            [
                'description' => null,
                'favourite' => true,
                'name' => 'recipe-name',
                'portions' => 1,
                'public' => true,
                'publicId' => 'public-id',
                'toDo' => false,
                'url' => null,
            ],
        );
        $user1EntityData2 = $user1EntityData->clone(['url' => 'url'], ['tags' => ['TAG1', 'TAG2']]);
        $user2EntityData = new EntityTestDataDto(
            EntityFactory::USER_EMAIL_2,
            [
                'description' => null,
                'name' => 'recipe-name',
                'portions' => 1,
                'public' => true,
                'publicId' => 'public-id',
                'url' => null,
            ],
        );

        return [
            '2 przepisy użytkownika i 3 innego -> 2 wyniki' => [
                [
                    $user1EntityData,
                    $user2EntityData,
                    $user2EntityData->clone(['name' => 'name2']),
                    $user2EntityData->clone(['name' => 'name3']),
                    $user1EntityData2->clone(['url' => 'url'], ['tags' => ['TAG1', 'TAG2']]),
                ],
            ],
            '7 przepisów użytkownika -> 7 wyników' => [
                [
                    $user1EntityData,
                    $user1EntityData2->clone(['name' => 'recipe-name2', 'url' => 'url'], ['tags' => ['TAG1', 'TAG2']]),
                    $user1EntityData2->clone(
                        ['name' => 'recipe-name3', 'description' => 'description', 'portions' => 2],
                        ['tags' => ['TAG1']],
                    ),
                    $user1EntityData2->clone(
                        ['name' => 'recipe-name4'],
                        [
                            'groups' => [
                                [
                                    'name' => 'name',
                                    'positions' => [
                                        [
                                            'additional' => false,
                                            'amount' => 1,
                                            'ingredient' => 'ingredient-name',
                                            'measure' => 'ml',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ),
                    $user1EntityData2->clone(
                        ['name' => 'recipe-name6'],
                        parameters: [
                        'groups' => [
                            [
                                'name' => 'name',
                                'positions' => [
                                    [
                                        'additional' => true,
                                        'amount' => 1,
                                        'ingredient' => 'ingredient-name',
                                        'measure' => 'ml',
                                    ],
                                    [
                                        'additional' => true,
                                        'amount' => 2,
                                        'measure' => 'szt',
                                        'recipe' => 'recipe-name2',
                                    ],
                                ],
                            ],
                        ],
                        ],
                    ),
                    $user1EntityData2->clone(
                        ['name' => 'recipe-name7'],
                        parameters: [
                        'groups' => [
                            [
                                'name' => 'name',
                                'positions' => [
                                    [
                                        'additional' => true,
                                        'amount' => 1,
                                        'ingredient' => 'ingredient-name',
                                        'measure' => 'ml',
                                    ],
                                    [
                                        'additional' => true,
                                        'amount' => 2,
                                        'measure' => 'szt',
                                        'recipe' => 'recipe-name2',
                                    ],
                                ],
                            ],
                            [
                                'positions' => [
                                    [
                                        'additional' => true,
                                        'amount' => 1,
                                        'ingredient' => 'ingredient-name',
                                        'measure' => 'ml',
                                    ],
                                ],
                            ],
                        ],
                        ],
                    ),
                    $user1EntityData2->clone(
                        ['name' => 'recipe-name8'],
                        parameters: [
                        'groups' => [
                            [
                                'name' => 'name',
                                'positions' => [
                                    [
                                        'additional' => true,
                                        'amount' => 1,
                                        'ingredient' => 'ingredient-name',
                                        'measure' => 'ml',
                                    ],
                                    [
                                        'additional' => true,
                                        'amount' => 2,
                                        'measure' => 'szt',
                                        'recipe' => 'recipe-name3',
                                    ],
                                ],
                            ],
                        ],
                        'photos' => [
                            [
                                'height' => 500,
                                'type' => 'image/jpg',
                                'width' => 100,
                            ],
                            [
                                'height' => 200,
                                'type' => 'image/png',
                                'width' => 100,
                            ],
                        ],
                        'timers' => [
                            [
                                'time' => 100,
                            ],
                            [
                                'name' => 'timer-name',
                                'time' => 200,
                            ],
                        ],
                        ],
                    ),
                ],
            ],
            'brak encji w bazie -> brak wyników' => [[]],
            'same przepisy innego użytkownika -> brak wyników' => [
                [
                    $user2EntityData,
                    $user2EntityData->clone(['name' => 'name2']),
                    $user2EntityData->clone(['name' => 'name3']),
                    $user2EntityData,
                ],
            ],
        ];
    }

    public static function resetPasswordInvalidTokenCases(): array
    {
        return [
            'błędny token' => [
                'lifetime' => null,
                'suffixReplace' => 'XYZ', // zamieniamy końcówkę tokena na coś losowego
                'toCheck' => null,
            ],
            'przeterminowany token (lifetime < 0)' => [
                'lifetime' => -100,
                'suffixReplace' => '',
                'toCheck' => null,
            ],
            'sprawdzanie obcego tokena' => [
                'lifetime' => null,
                'suffixReplace' => '',
                'toCheck' => 'DStQUU7Iksepk03fIcwvaopURqBj6x7mZnFcrLqd',
            ],
        ];
    }

    public static function resetPasswordUserNotFoundCases(): array
    {
        return [
            'brak użytkownika o podanym adresie e-mail' => [
                'fbId' => null,
                'requestEmail' => EntityFactory::USER_EMAIL_3,
                'userEmail' => EntityFactory::USER_EMAIL_2, // istniejący user
            ],
            'użytkownik istnieje, ale ma konto FB (reset hasła niedostępny)' => [
                'fbId' => 123,
                'requestEmail' => EntityFactory::USER_EMAIL_2,
                'userEmail' => EntityFactory::USER_EMAIL_2, // istniejący user
            ],
        ];
    }

    public static function seasonIndexData(): array
    {
        $user1EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL, ['start' => 1, 'stop' => 12]);
        $user2EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL_2);

        return [
            '2 sezony użytkownika i 1 innego -> 2 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['start' => 3]),
                    $user2EntityData,
                ],
            ],
            '3 sezony użytkownika -> 3 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['start' => 3]),
                    $user1EntityData->clone(['stop' => 5]),
                ],
            ],
            '3 sezony użytkownika i 4 innego -> 3 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['start' => 3]),
                    $user1EntityData->clone(['stop' => 5]),
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
            'brak encji w bazie -> brak wyników' => [[]],
            'same sezony innego użytkownika -> brak wyników' => [
                [
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
        ];
    }

    public static function settingsData(): array
    {
        $settings = new EntityTestDataDto(
            EntityFactory::USER_EMAIL_2,
            ['ozaKey' => null, 'autocomplete' => true],
            ['fbId' => null],
        );

        return [
            'standardowy user, autocomplete = false, z ozaKey' => [
                $settings->clone(['ozaKey' => 'key', 'autocomplete' => false]),
            ],
            'standardowy user, autocomplete = true, brak ozaKey' => ['settings' => $settings],
            'użytkownik FB, autocomplete = false' => [
                $settings->clone(['autocomplete' => false], ['fbId' => 'abc']),
            ],
        ];
    }

    public static function suppliesResponseData(): array
    {
        return [
            'pusty response -> brak zapasów' => [
                // przetworzone dane oczekiwane w odpowiedzi API
                'expectedData' => [],
                // surowa odpowiedź z API OZA
                'responseData' => '{}',
            ],
            'response z jednym zapasem' => [
                // przetworzone dane oczekiwane w odpowiedzi API
                'expectedData' => [
                    [
                        'amount' => '850ml',
                        'available' => true,
                        'id' => 79,
                        'name' => 'Produkt 1',
                    ],
                ],
                // surowa odpowiedź z API OZA
                'responseData' => '[{
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
            ],
        ];
    }

    public static function tagIndexData(): array
    {
        $user1EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL);
        $user2EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL_2);

        return [
            '2 tagi użytkownika i 1 innego -> 2 wyniki' => [
                [
                    $user1EntityData->clone(['name' => 'TAG1']),
                    $user1EntityData->clone(['name' => 'TAG2']),
                    $user2EntityData,
                ],
            ],
            '3 tagi użytkownika -> 3 wyniki' => [
                [
                    $user1EntityData->clone(['name' => 'TAG1']),
                    $user1EntityData->clone(['name' => 'TAG2']),
                    $user1EntityData->clone(['name' => 'TAG3']),
                ],
            ],
            '3 tagi użytkownika i 4 innego -> 3 wyniki' => [
                [
                    $user1EntityData->clone(['name' => 'TAG1']),
                    $user1EntityData->clone(['name' => 'TAG2']),
                    $user1EntityData->clone(['name' => 'TAG3']),
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
            'brak encji w bazie -> brak wyników' => [[]],
            'same tagi innego użytkownika -> brak wyników' => [
                [
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
        ];
    }

    public static function timerIndexData(): array
    {
        $user1EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL, ['name' => 'name', 'time' => 100]);
        $user2EntityData = new EntityTestDataDto(EntityFactory::USER_EMAIL_2);

        return [
            '1 timer usera (powiązany z recipe -> pominięty) i 2 innego, brak wyników' => [
                [
                    $user2EntityData,
                    $user2EntityData,
                    $user1EntityData->clone(parameters: ['recipe' => true]),
                ],
            ],
            '2 timery usera i 1 innego -> 2 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(['name' => 'name2']),
                    $user2EntityData,
                ],
            ],
            '3 timery, 1 powiązany z recipe -> pominięty -> 2 wyniki' => [
                [
                    $user1EntityData->clone(parameters: ['recipe' => true]),
                    $user1EntityData->clone(['name' => 'name2']),
                    $user1EntityData->clone(['time' => 100]),
                ],
            ],
            '4 timery usera (1 powiązany z recipe -> pominięty) i 4 innego -> 3 wyniki' => [
                [
                    $user1EntityData,
                    $user1EntityData->clone(parameters: ['recipe' => true]),
                    $user1EntityData->clone(['name' => 'name2']),
                    $user1EntityData->clone(['time' => 100]),
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
            'brak encji w bazie -> brak wyników' => [[]],
            'same timery innego użytkownika -> brak wyników' => [
                [
                    $user2EntityData,
                    $user2EntityData,
                    $user2EntityData,
                ],
            ],
        ];
    }

    public static function unavailableIngredientsCases(): array
    {
        return [
            'brak encji w bazie' => [
                [],
            ],
            'szukana encja należy do innego użytkownika' => [
                [
                    ['email' => 'email@example.com', 'ozaId' => 1],
                    ['email' => 'email@example.com', 'ozaId' => 2],
                    ['email' => 'email@example.com', 'ozaId' => 3],
                    ['email' => 'email2@example.com', 'ozaId' => 4],
                    ['email' => 'email2@example.com', 'ozaId' => 5],
                    ['email' => 'email2@example.com', 'ozaId' => 6],
                    ['email' => 'email2@example.com', 'ozaId' => 7, 'toFind' => true],
                ],
            ],
            'szukana encja nie istnieje' => [
                [
                    ['email' => 'email@example.com', 'ozaId' => 1],
                    ['email' => 'email@example.com', 'ozaId' => 2],
                    ['email' => 'email@example.com', 'ozaId' => 3],
                ],
            ],
        ];
    }

    public static function unavailableTimersCases(): array
    {
        return [
            'brak encji w bazie' => [
                [],
            ],
            'encja powiązana z przepisem' => [
                [
                    ['email' => 'email@example.com', 'recipe' => true],
                ],
            ],
            'szukana encja należy do innego użytkownika' => [
                [
                    ['email' => 'email@example.com'],
                    ['email' => 'email@example.com'],
                    ['email' => 'email@example.com'],
                    ['email' => 'email2@example.com'],
                    ['email' => 'email2@example.com'],
                    ['email' => 'email2@example.com'],
                    ['email' => 'email2@example.com', 'toFind' => true],
                ],
            ],
            'szukana encja nie istnieje' => [
                [
                    ['email' => 'email@example.com'],
                    ['email' => 'email@example.com'],
                    ['email' => 'email@example.com'],
                ],
            ],
        ];
    }

    public static function updateIngredientValidValues(): array
    {
        return [
            'brak zmian' => [[]],
            'ustawia available = false' => [['available' => false]],
            'ustawia available = true' => [['available' => true]],
            'ustawia ozaId = 0 (reset)' => [['ozaId' => 0]],
            'ustawia ozaId = 100' => [['ozaId' => 100]],
            'ustawia ozaId = null' => [['ozaId' => null]],
            'zmiana nazwy' => [['name' => 'name2']],
            'zmienia nazwę, available = true i ozaId = 100' =>  [
                ['name' => 'name2', 'available' => true, 'ozaId' => 100],
            ],
            'zmienia nazwę i available = true' => [['name' => 'name', 'available' => true]],
            'zmienia nazwę i ozaId = 100' => [['name' => 'name2', 'ozaId' => 100]],
        ];
    }
}
