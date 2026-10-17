<?php

namespace App\Tests\DataProvider;

use App\Config\LengthConfig;
use App\Dto\List\Type\StringList;
use App\Dto\Request\List\RecipePositionList;
use App\Dto\Request\List\RecipePositionsGroupList;
use App\Dto\Request\RecipePosition;
use App\Dto\Request\RecipePositionsGroup;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\ValidationErrors;

class ValidationDataProvider
{
    public static function acceptableEmailCases(): iterable
    {
        return [
            'email inny niż istniejące (w tym FB), więc unikalny' => [
                'emailToValidate' => 'another@example.com',
                'existingUsers' => [
                    ['email' => 'someone@example.com', 'fbId' => null],
                    ['email' => 'fbuser@example.com', 'fbId' => 'xyz'],
                ],
            ],
            'email istnieje, ale powiązany z kontem FB' => [
                'emailToValidate' => 'fbuser@example.com',
                'existingUsers' => [
                    ['email' => 'fbuser@example.com', 'fbId' => '123456789'],
                ],
            ],
            'nowy email, brak użytkowników w bazie' => [
                'emailToValidate' => 'totally.new@example.com',
                'existingUsers' => [],
            ],
        ];
    }

    public static function inaccessibleTagCases(): array
    {
        return [
            'brak tagów u użytkownika' => [
                'anotherUsersData' => [],
                'tagToValidate' => 'TAG_NAME',
                'userTags' => [],
            ],
            'tag istnieje, ale należy do innego użytkownika' => [
                'anotherUsersData' => [
                    [
                        'email' => 'email2@example.com',
                        'tags' => ['TAG1', 'TAG2', 'TAG3', 'TAG_NAME'],
                    ],
                ],
                'tagToValidate' => 'TAG_NAME',
                'userTags' => ['TAG1', 'TAG2', 'TAG3'],
            ],
            'użytkownik ma inne tagi, ale nie ten szukany' => [
                'anotherUsersData' => [],
                'tagToValidate' => 'TAG_NAME',
                'userTags' => ['TAG1', 'TAG2', 'TAG3'],
            ],
        ];
    }

    public static function invalidEditSeasonValues(): array
    {
        return [
            'stop mniejszy niż start' => [
                'start' => 12,
                'stop' => 3,
            ],
            'stop równy start' => [
                'start' => 5,
                'stop' => 5,
            ],
        ];
    }

    public static function invalidIngredientRecipePairValues(): array
    {
        return [
            'oba pola null' => [
                'ingredient' => null,
                'recipe' => null,
            ],
            'oba pola wypełnione' => [
                'ingredient' => 1,
                'recipe' => 2,
            ],
        ];
    }

    public static function invalidPhotoValues(): array
    {
        return [
            'brak prefixu data:image/png;base64,' => [
                base64_encode('Hello World'),
                ValidationErrors::INVALID_BASE64_STRING_PREFIX,
            ],
            'content zawiera HTML' => [
                'data:image/png;base64,<img src="...">',
                ValidationErrors::INVALID_BASE64_STRING_CONTENT,
            ],
            'content z niedozwolonymi znakami (#$%)' => [
                'data:image/png;base64,###$$$%%%',
                ValidationErrors::INVALID_BASE64_STRING_CONTENT,
            ],
            'content z niedozwolonym znakiem (!)' => [
                'data:image/png;base64,SGVsbG8hIQ!',
                ValidationErrors::INVALID_BASE64_STRING_CONTENT,
            ],
            'content z niepoprawnym paddingiem (====)' => [
                'data:image/png;base64,U29tZWRhdGE====',
                ValidationErrors::INVALID_BASE64_STRING_CONTENT,
            ],
            'pusty content po prefixie' => [
                'data:image/png;base64,',
                ValidationErrors::INVALID_BASE64_STRING_CONTENT,
            ],
        ];
    }

    public static function invalidReorderPhotosCountValues(): array
    {
        return [
            '2 zdjęcia w przepisie, 3 w żądaniu' => [2, 3],
            '3 zdjęcia w przepisie, brak w żądaniu' => [3, 0],
            'brak zdjęć w przepisie, 1 w żądaniu' => [0, 1],
        ];
    }

    public static function outOfRangeSeasonValues(): array
    {
        return [
            'wartość powyżej maksymalnego miesiąca' => [13],
            'wartość równa zero' => [0],
            'wartość ujemna' => [-100],
        ];
    }

    public static function passwordChangePairsValues(): array
    {
        return [
            'hasła z emoji' => ['Pączek🍩121', 'Pączek🍩123'],
            'hasła z tabami i newline' => ["abc\t\n", "abc\t123\n"],
            'maksymalna długość vs. inne' =>  [
                str_repeat('X', LengthConfig::PASSWORD),
                str_repeat('x', LengthConfig::PASSWORD),
            ],
            'nowe hasło ze spacją' => ['old-password', 'pass with space'],
            'nowe hasło z unicode i znakami specjalnymi' =>  ['password', 'Świeżak#123!漢字'],
            'oba hasła ze znakami specjalnymi' =>  ['aaa%$&*()', '@!#%$&*()'],
            'pojedyncze znaki' =>  ['b', 'a'],
            'różne stare i nowe hasło' =>  ['old-pasword', 'abc123'],
        ];
    }

    public static function recipeGroupsInputAndExpectedValues(): array
    {
        return [
            'dwie grupy: bez nazwy (2 pozycje - składnik i przepis) i "group3" (1 pozycja składnik)' => [
                [
                    'groups' => [
                        [
                            'positions' => [
                                [
                                    'additional' => true,
                                    'amount' => 2.0,
                                    'ingredient' => 1,
                                    'measure' => 'szt',
                                    'recipe' => null,
                                ],
                                [
                                    'additional' => false,
                                    'ingredient' => null,
                                    'measure' => 'ml',
                                    'recipe' => 2,
                                ],
                            ],
                        ],
                        [
                            'name' => 'group3',
                            'positions' => [
                                [
                                    'additional' => true,
                                    'amount' => 2.0,
                                    'ingredient' => 3,
                                    'measure' => 'szt',
                                    'recipe' => null,
                                ],
                            ],
                        ],
                    ],
                ],
                new RecipePositionsGroupList(
                    new RecipePositionsGroup(null, new RecipePositionList(
                        new RecipePosition(
                            true,
                            2.0,
                            'szt',
                            EntityFactory::getSimpleIngredient(1),
                            null,
                        ),
                        new RecipePosition(
                            false,
                            null,
                            'ml',
                            null,
                            EntityFactory::getSimpleRecipe(2),
                        ),
                    )),
                    new RecipePositionsGroup('group3', new RecipePositionList(
                        new RecipePosition(
                            true,
                            2.0,
                            'szt',
                            EntityFactory::getSimpleIngredient(3),
                            null,
                        ),
                    )),
                ),
            ],
            'jedna grupa "group2", 1 pozycja (składnik)' => [
                [
                    'groups' => [
                        [
                            'name' => 'group2',
                            'positions' => [
                                [
                                    'additional' => false,
                                    'amount' => 1.0,
                                    'ingredient' => 1,
                                    'measure' => 'szt',
                                    'recipe' => null,
                                ],
                            ],
                        ],
                    ],
                ],
                new RecipePositionsGroupList(
                    new RecipePositionsGroup(
                        'group2',
                        new RecipePositionList(
                            new RecipePosition(
                                false,
                                1.0,
                                'szt',
                                EntityFactory::getSimpleIngredient(1),
                                null,
                            ),
                        ),
                    ),
                ),
            ],
            'jedna grupa bez nazwy, 2 pozycje (składnik i przepis)' => [
                [
                    'groups' => [
                        [
                            'name' => null,
                            'positions' => [
                                [
                                    'additional' => true,
                                    'amount' => 2.0,
                                    'ingredient' => 1,
                                    'measure' => 'szt',
                                    'recipe' => null,
                                ],
                                [
                                    'additional' => true,
                                    'amount' => 1.0,
                                    'ingredient' => null,
                                    'measure' => 'ml',
                                    'recipe' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
                new RecipePositionsGroupList(
                    new RecipePositionsGroup(
                        null,
                        new RecipePositionList(
                            new RecipePosition(
                                true,
                                2.0,
                                'szt',
                                EntityFactory::getSimpleIngredient(1),
                                null,
                            ),
                            new RecipePosition(
                                true,
                                1.0,
                                'ml',
                                null,
                                EntityFactory::getSimpleRecipe(2),
                            ),
                        ),
                    ),
                ),
            ],
        ];
    }

    public static function uniqueTagNameCases(): array
    {
        return [
            'brak istniejących' =>  [[], 'uniqueName'],
            'jeden istniejący, inna nazwa' =>  [['tagName'], 'TAGNAME2'],
            'kilka istniejących, nowa' =>  [['tag1', 'tag2'], 'tag3'],
            'różne nazwy' =>  [['tag', 'tagX'], 'tagY'],
        ];
    }

    public static function uniqueUppercaseTagsValues(): array
    {
        return [
            'brak tagów' =>  [[], new StringList(...[])],
            'duplikaty w jednej formie' =>  [['foo', 'foo'], new StringList(...['FOO'])],
            'duplikaty w różnych formach' =>  [['foo', 'FOO', 'Foo'], new StringList(...['FOO'])],
            'dwa różne tagi' =>  [['foo', 'bar'], new StringList(...['FOO', 'BAR'])],
            'mieszane duplikaty z innym słowem' =>  [['abc', 'ABC', 'def'], new StringList(...['ABC', 'DEF'])],
            'pojedynczy tag' =>  [['test'], new StringList(...['TEST'])],
        ];
    }

    public static function validCreateIngredientValues(): array
    {
        return [
            'brak ozaId i name minimalne' => [
                [
                    'available' => false,
                    'name' => 'Ogórek',
                ],
            ],
            'brak ozaId – tylko name i available' => [
                [
                    'available' => true,
                    'name' => 'Pomidor',
                ],
            ],
            'nazwa o maksymalnej długości (unicode)' => [
                [
                    'available' => true,
                    'name' => str_repeat('西', LengthConfig::INGREDIENT),
                ],
            ],
            'nazwa z nadmiarowymi spacjami (trimowana)' => [
                [
                    'available' => true,
                    'name' => '  ' . str_repeat('a', LengthConfig::INGREDIENT) . '   ',
                ],
            ],
            'ozaId = 0 zostaje zamienione na null' => [
                [
                    'available' => false,
                    'name' => 'Ziemniak',
                    'ozaId' => 0,
                ],
            ],
            'pełny zestaw danych z ozaId = 123' => [
                [
                    'available' => true,
                    'name' => 'Marchewka',
                    'ozaId' => 123,
                ],
            ],
        ];
    }

    public static function validCreateSeasonValues(): array
    {
        return [
            'sezon krótki (1-2), składnik id = 6' => [
                'ingredientId' => 6,
                'start' => 1,
                'stop' => 2,
            ],
            'sezon wiosenny (3-5), składnik id = 4' => [
                'ingredientId' => 4,
                'start' => 3,
                'stop' => 5,
            ],
            'sezon zimowy (11-12), składnik id = 100' => [
                'ingredientId' => 100,
                'start' => 11,
                'stop' => 12,
            ],
        ];
    }

    public static function validEditIngredientValues(): array
    {
        return [
            'częściowe dane — nazwa, dostępny' => [
                [
                    'available' => true,
                    'name' => 'Marchewka',
                ],
            ],
            'dostępność false i ozaId = 0 (czyli null)' => [
                [
                    'available' => false,
                    'ozaId' => 0,
                ],
            ],
            'maksymalna długość nazwy (unicode)' => [
                ['name' => ' ' . str_repeat('西', LengthConfig::INGREDIENT) . ' '],
            ],
            'pełne dane — dostępny, nazwa z ozaId' => [
                [
                    'available' => true,
                    'name' => 'Czosnek',
                    'ozaId' => 42,
                ],
            ],
            'pusty payload' => [[]],
            'tylko dostępność — false' => [['available' => false]],
            'tylko nazwa ' => [['name' => 'Cebula']],
            'tylko ozaId — 99' => [['ozaId' => 99]],
        ];
    }

    public static function validEditSeasonValues(): array
    {
        return [
            'zakres 1–2' => [
                'start' => 1,
                'stop' => 2,
            ],
            'zakres 3–5' => [
                'start' => 3,
                'stop' => 5,
            ],
            'zakres 11-12' => [
                'start' => 11,
                'stop' => 12,
            ],
        ];
    }

    public static function validEmailValues(): array
    {
        return [
            'bez spacji' => ['test@example.com'],
            'ze spacją na końcu' => ['test@example.com '],
            'ze spacją na początku' => [' test@example.com'],
            'z obustronnymi spacjami' => [' test@example.com '],
        ];
    }

    public static function validIngredientRecipePairValues(): array
    {
        return [
            'tylko ingredient podany' => [
                'ingredient' => 3,
                'recipe' => null,
            ],
            'tylko recipe podane' => [
                'ingredient' => null,
                'recipe' => 2,
            ],
        ];
    }

    public static function validOzaKeyValues(): array
    {
        return [
            'brak klucza (null)' => [null],
            'krótki tekst' => ['key'],
            'maksymalna długość' => [str_repeat('a', LengthConfig::OZA_KEY)],
            'maksymalna długość (znaki wielobajtowe 西)' => [str_repeat('西', LengthConfig::OZA_KEY)],
            'pojedynczy znak' => ['a'],
            'same znaki specjalne' => [str_repeat('#', LengthConfig::OZA_KEY)],
            'spacja + maksymalna długość (litery)' => [
                ' ' . str_repeat('a', LengthConfig::OZA_KEY) . '   ',
            ],
            'spacja + maksymalna długość (znaki unicode)' => [
                ' ' . str_repeat('西', LengthConfig::OZA_KEY) . '   ',
            ],
        ];
    }

    public static function validPasswordValues(): array
    {
        return [
            'alfanumeryczne hasło' => ['abc123'],
            'emoji w haśle' => ['Pączek🍩123'],
            'maksymalna długość' => [str_repeat('x', LengthConfig::PASSWORD)],
            'pojedynczy znak' => ['a'],
            'same znaki specjalne' => ['@!#%$&*()'],
            'spacja w haśle' => ['pass with space'],
            'znaki kontrolne (tab, newline)' => ["abc\t123\n"],
            'znaki unicode i specjalne' => ['Świeżak#123!漢字'],
        ];
    }

    public static function validPhotoSizesValues(): array
    {
        return [
            'dokładnie minimalne wymiary' => [
                'height' => 600,
                'width' => 800,
            ],
            'minimalna wysokość + szerszy obraz' => [
                'height' => 600,
                'width' => 805,
            ],
            'minimalna wysokość + znacznie szerszy obraz' => [
                'height' => 600,
                'width' => 810,
            ],
            'wyższy obraz przy minimalnej szerokości' => [
                'height' => 601,
                'width' => 800,
            ],
        ];
    }

    public static function validPhotoValues(): array
    {
        return [
            'krótki tekst jako obrazek' => ['data:image/png;base64,', base64_encode('Hello World')],
            'losowe 64 bajty jako obrazek' => ['data:image/png;base64,', base64_encode(random_bytes(64))],
        ];
    }

    public static function validRegisterValues(): array
    {
        return [
            'email z plusem (filtr)' => [
                'email' => 'test+filter@domain.pl',
                'first' => 'haslo1234',
                'second' => 'haslo1234',
            ],
            'email z unicode (żółw)' => [
                'email' => 'żółw@domena.pl',
                'first' => 'unicodeSafePass1',
                'second' => 'unicodeSafePass1',
            ],
            'maksymalna długość email i hasła' => [
                'email' => str_repeat('a', 64) . '@' . str_repeat('b', 63) . '.' . str_repeat('c', 61) . '.pl',
                'first' => str_repeat('x', LengthConfig::PASSWORD),
                'second' => str_repeat('x', LengthConfig::PASSWORD),
            ],
            'zwykły użytkownik' => [
                'email' => 'user@example.com',
                'first' => 'securePassword123',
                'second' => 'securePassword123',
            ],
        ];
    }

    public static function validTagValues(): array
    {
        return [
            'maksymalna długość (#)' => [str_repeat('#', LengthConfig::TAG)],
            'maksymalna długość (a)' => [str_repeat('a', LengthConfig::TAG)],
            'maksymalna długość (a) z białymi znakami' => [
                ' ' . str_repeat('a', LengthConfig::TAG) . '   ',
            ],
            'maksymalna długość (znaki wielobajtowe 西)' => [str_repeat('西', LengthConfig::TAG)],
            'maksymalna długość (西) z białymi znakami' => [
                ' ' . str_repeat('西', LengthConfig::TAG) . '   ',
            ],
            'minimalna nazwa' => ['a'],
            'normalna nazwa ze spacją' => ['tag name'],
        ];
    }

    public static function validTimerValues(): array
    {
        return [
            'maksymalna długość, 100 sekund' => [str_repeat('a', LengthConfig::TIMER), 100],
            'maksymalna długość z białymi znakami, 200 sekund' => [
                ' ' . str_repeat('a', LengthConfig::TIMER) . '   ',
                200,
            ],
            'minimalna nazwa, 1 sekunda' => ['a', 1],
            'normalna nazwa, 100 sekund' => ['timer name', 100],
            'znaki wielobajtowe (西), 80 sekund' => [' ' . str_repeat('西', LengthConfig::TIMER), 80],
        ];
    }

    public static function validTokenValues(): array
    {
        return [
            'krótki token' => ['token'],
            'maksymalna długość (255 znaków)' => [str_repeat('a', 255)],
            'maksymalna długość z białymi znakami' => [' ' . str_repeat('a', 255) . '   '],
            'pojedynczy znak' => ['a'],
        ];
    }
}
