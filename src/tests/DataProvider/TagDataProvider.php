<?php

namespace App\Tests\DataProvider;

class TagDataProvider
{
    public static function caseInsensitiveNameValues(): array
    {
        return [
            'dokładnie taka sama nazwa' => [
                'TAGNAME', // istniejąca wartość
                'TAGNAME', // szukana/testowana wartość
            ],
            'inna wielkość liter – małe' => [
                'TAGNAME', // istniejąca wartość
                'tagname', // szukana/testowana wartość
            ],
            'inna wielkość liter – mieszane' => [
                'TAGNAME', // istniejąca wartość
                'TaGnAmE', // szukana/testowana wartość
            ],
        ];
    }
}
