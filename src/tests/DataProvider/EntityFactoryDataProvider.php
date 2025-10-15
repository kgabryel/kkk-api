<?php

namespace App\Tests\DataProvider;

use App\Entity\User;
use App\Tests\Helper\EntityFactory;
use stdClass;

class EntityFactoryDataProvider
{
    public static function ingredientDataValues(): array
    {
        return [
            'długa nazwa, dostępny = true, ozaId = null' => [
                'ingredientName' => 'ingredient name',
                'isAvailable' => true,
                'ozaId' => null,
            ],
            'inna nazwa, dostępny = false, ozaId = 20' => [
                'ingredientName' => 'name 2',
                'isAvailable' => false,
                'ozaId' => 20,
            ],
            'prosta nazwa, dostępny = false, ozaId = 1' => [
                'ingredientName' => 'name',
                'isAvailable' => false,
                'ozaId' => 1,
            ],
        ];
    }

    public static function invalidEntitiesValues(): array
    {
        return [
            'encja User' => [new User()],
            'obiekt stdClass' => [new stdClass()],
        ];
    }

    public static function seasonDataValues(): array
    {
        return [
            'składnik domyślny, zakres 1–2' => [
                'ingredient' => EntityFactory::getSimpleIngredient(),
                'start' => 1,
                'stop' => 2,
            ],
            'składnik id = 2, zakres 3–12' => [
                'ingredient' => EntityFactory::getSimpleIngredient(2),
                'start' => 3,
                'stop' =>  12,
            ],
        ];
    }

    public static function tagNameValues(): array
    {
        return [
            'małe litery' => ['tag'],
            'wielkie litery' => ['TAG VALUE'],
        ];
    }

    public static function timerDataValues(): array
    {
        return [
            'dłuższa nazwa, czas = 1' => [
                'time' => 1,
                'timerName' => 'timer name',
            ],
            'prosta nazwa, czas = 0' => [
                'time' => 0,
                'timerName' => 'name',
            ],
            'pusta nazwa, czas = 100' => [
                'time' => 100,
                'timerName' => '',
            ],
        ];
    }
}
