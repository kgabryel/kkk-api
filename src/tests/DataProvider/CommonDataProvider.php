<?php

namespace App\Tests\DataProvider;

use stdClass;

class CommonDataProvider
{
    public static function blankValues(): array
    {
        return [
            'pusty string' => [''],
            'wartość false' => [false],
            'wartość null' => [null],
        ];
    }

    public static function boolValues(): array
    {
        return [
            'wartość false' => [false],
            'wartość true' => [true],
        ];
    }

    public static function invalidConfigValues(): array
    {
        return [
            'null' => [null],
            'pusta tablica' => [[]],
            'pusty string' => [''],
            'wartość false' => [false],
            'wartość true' => [true],
        ];
    }

    public static function invalidEmails(): array
    {
        return [
            'adres kończy się kropką' => ['user@domain.com.'],
            'adres zaczyna się kropką' => ['.user@domain.com'],
            'brak domeny' => ['user@'],
            'brak nazwy użytkownika' => ['@domain.com'],
            'brak TLD' => ['user@domain'],
            'brak znaku @' => ['invalidemail.com'],
            'HTML injection' => ['<script>@domain.com'],
            'podwójna kropka w domenie' => ['user@domain..com'],
            'spacje w adresie' => ['user name@domain.com'],
            'wielokrotny znak @' => ['user@@domain.com'],
            'znak specjalny w domenie' => ['user@dom!ain.com'],
        ];
    }

    public static function invalidNonBlankArrayValues(): array
    {
        return [
            'funkcja anonimowa' => [fn (): int => 42],
            'liczba całkowita' => [1],
            'liczba zmiennoprzecinkowa' => [3.14],
            'obiekt stdClass' => [new stdClass()],
            'string numeryczny' => ['123'],
            'string tekstowy' => ['hello'],
            'wartość bool true' => [true],
        ];
    }

    public static function invalidNonBlankIntegerValues(): array
    {
        return [
            'funkcja anonimowa' => [fn (): int => 42],
            'liczba zmiennoprzecinkowa' => [3.14],
            'obiekt stdClass' => [new stdClass()],
            'string numeryczny' => ['123'],
            'string tekstowy' => ['hello'],
            'tablica' => [[1, 2, 3]],
            'wartość bool true' => [true],
        ];
    }

    public static function invalidNonBlankNumberValues(): array
    {
        return [
            'funkcja anonimowa' => [fn (): int => 42],
            'obiekt stdClass' => [new stdClass()],
            'string numeryczny' => ['123'],
            'string tekstowy' => ['hello'],
            'tablica' => [[1, 2, 3]],
            'wartość bool true' => [true],
        ];
    }

    public static function invalidNonBlankStringValues(): array
    {
        return [
            'funkcja anonimowa' => [fn (): string => 'hello'],
            'liczba całkowita' => [123],
            'liczba zmiennoprzecinkowa' => [3.14],
            'obiekt stdClass' => [new stdClass()],
            'tablica' => [[1, 2, 3]],
            'wartość bool true' => [true],
        ];
    }

    public static function invalidUrls(): array
    {
        return [
            'brak TLD' => ['http://my-machine'],
            'localhost bez TLD' => ['http://localhost'],
            'niepoprawny format' => ['notaurl'],
            'brak protokołu' => ['example.com'],
            'tylko protokół bez hosta' => ['https://'],
            'protokół nieobsługiwany (ftp)' => ['ftp://example.com'],
            'spacja w adresie' => ['https://exa mple.com'],
            'protokół z literówką' => ['htps://example.com'],
            'brak kropki w domenie' => ['https://example'],
            'tylko host, bez protokołu' => ['www.example.com'],
            'podwójny slash bez protokołu' => ['//example.com'],
            'pusty protokół' => ['://example.com'],
            'URL z niepoprawnym portem' => ['https://example.com:99999'],
            'URL z przecinkiem w porcie' => ['https://example.com:80,90'],
            'spacja na końcu' => ['https://example.com '],
            'spacja na początku' => [' https://example.com'],
            'backslash w ścieżce' => ['https://example.com\\test'],
            'podwójny @ w adresie' => ['https://user@@example.com'],
            'podwójne kropki w domenie' => ['https://example..com'],
            'brak hosta po protokole' => ['https:///path']
        ];
    }

    public static function lessThanOrEqualZero(): array
    {
        return [
            'duża liczba ujemna' => [-9999],
            'liczba -1' => [-1],
            'liczba -5' => [-5],
            'liczba 0' => [0],
            'minimalna wartość int' => [PHP_INT_MIN],
        ];
    }

    public static function lessThanOrEqualZeroFloat(): array
    {
        return [
            'duża liczba ujemna (float)' => [-9999.0],
            'liczba -1.0' => [-1.0],
            'liczba -5.0' => [-5.0],
            'liczba 0.0' => [0.0],
            'mała wartość ujemna' => [-0.0001],
        ];
    }

    public static function notBoolValuesExpectNull(): array
    {
        return [
            'funkcja anonimowa' => [fn (): true => true],
            'inny int' => [123],
            'int 0' => [0],
            'int 1' => [1],
            'liczba zmiennoprzecinkowa' => [3.14],
            'obiekt stdClass' => [new stdClass()],
            'string "0"' => ['0'],
            'string "1"' => ['1'],
            'string "false"' => ['false'],
            'string "true"' => ['true'],
            'string losowy' => ['hello'],
            'tablica' => [[true]],
        ];
    }

    public static function oppositeBoolValues(): array
    {
        return [
            'false/true' => [false, true],
            'true/false' => [true, false],
        ];
    }

    public static function provideDataWithMissingEntity(): array
    {
        return [
            'brak encji w bazie' => [
                [],
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
}
