<?php

namespace App\Tests\DataProvider;

use App\Dto\List\Type\StringList;
use App\Dto\Request\List\OrderList;
use App\Dto\Request\List\RecipePositionList;
use App\Dto\Request\List\RecipePositionsGroupList;
use App\Dto\Request\List\TimerList;
use App\Dto\Request\Order;
use App\Dto\Request\Recipe as RecipeRequest;
use App\Dto\Request\RecipePosition;
use App\Dto\Request\RecipePositionsGroup;
use App\Dto\Request\Timer;
use App\Dto\Request\Timer as TimerRequest;
use App\Entity\RecipePosition as RecipePositionEntity;
use App\Entity\RecipePositionGroup;
use App\Tests\Helper\EntityFactory;
use Doctrine\Common\Collections\ArrayCollection;

class RecipeDataProvider
{
    public static function flagValues(): array
    {
        return [
            'favourite = false, toDo = false' => [
                'favourite' => false,
                'toDo' => false,
            ],
            'favourite = false, toDo = null' => [
                'favourite' => false,
                'toDo' => null,
            ],
            'favourite = false, toDo = true' => [
                'favourite' => false,
                'toDo' => true,
            ],
            'favourite = null, toDo = false' => [
                'favourite' => null,
                'toDo' => false,
            ],
            'favourite = null, toDo = null' => [
                'favourite' => null,
                'toDo' => null,
            ],
            'favourite = null, toDo = true' => [
                'favourite' => null,
                'toDo' => true,
            ],
            'favourite = true, toDo = false' => [
                'favourite' => true,
                'toDo' => false,
            ],
            'favourite = true, toDo = null' => [
                'favourite' => true,
                'toDo' => null,
            ],
            'favourite = true, toDo = true' => [
                'favourite' => true,
                'toDo' => true,
            ],
        ];
    }

    public static function photoValues(): array
    {
        return [
            'lista z 1 zdjęciem' =>  [[EntityFactory::getSimplePhoto()]],
            'lista z 3 zdjęciami' => [
                [
                    EntityFactory::getSimplePhoto(1),
                    EntityFactory::getSimplePhoto(2),
                    EntityFactory::getSimplePhoto(3),
                ],
            ],
            'pusta lista zdjęć' => [[]],
        ];
    }

    public static function positionPartsValues(): array
    {
        return [
            'ingredient i recipe ustawione -> wybiera ingredient' => [
                'expected' => 'ingredient name',
                'ingredient' => 'ingredient name',
                'recipe' => 'recipe name',
            ],
            'oba null -> pusty string' => [
                'expected' => '',
                'ingredient' => null,
                'recipe' => null,
            ],
            'tylko ingredient ustawiony -> wybiera ingredient' => [
                'expected' => 'ingredient name',
                'ingredient' => 'ingredient name',
                'recipe' => null,
            ],
            'tylko recipe ustawione -> wybiera recipe' => [
                'expected' => 'recipe name',
                'ingredient' => null,
                'recipe' => 'recipe name',
            ],
        ];
    }

    public static function positionsGroupsAndTimersValues(): array
    {
        return [
            'brak grup i brak timerów' => [new ArrayCollection(), new ArrayCollection()],
            'grupy pozycji (2 szt.) i timery (3 szt.)' => [
                new ArrayCollection(
                    [
                        EntityFactory::getSimpleRecipePositionGroup(1),
                        EntityFactory::getSimpleRecipePositionGroup(2),
                    ],
                ),
                new ArrayCollection(
                    [
                        EntityFactory::getSimpleTimer(1),
                        EntityFactory::getSimpleTimer(2),
                        EntityFactory::getSimpleTimer(3),
                    ],
                )],
            'tylko grupy pozycji' => [
                new ArrayCollection([EntityFactory::getSimpleRecipePositionGroup()]),
                new ArrayCollection(),
            ],
            'tylko timery' => [new ArrayCollection(), new ArrayCollection([EntityFactory::getSimpleTimer()])],
        ];
    }

    public static function reorderPhotosValues(): array
    {
        return [
            '1 zdjęcie -> [1->1] -> kolejność [1]' => [
                'recipePhotos' => [
                    1 => EntityFactory::getSimplePhoto(1),
                ],
                'request' => new OrderList(new Order(1, 1)),
            ],
            '2 zdjęcia -> [1->1, 2->2] -> kolejność [1, 2]' => [
                'recipePhotos' => [
                    1 => EntityFactory::getSimplePhoto(1),
                    2 => EntityFactory::getSimplePhoto(2),
                ],
                'request' => new OrderList(new Order(1, 1), new Order(2, 2)),
            ],
            '3 zdjęcia -> [3->1, 1->3, 2->2] -> kolejność [3, 2, 1]' => [
                'recipePhotos' => [
                    1 => EntityFactory::getSimplePhoto(1),
                    2 => EntityFactory::getSimplePhoto(2),
                    3 => EntityFactory::getSimplePhoto(3),
                ],
                'request' => new OrderList(
                    new Order(3, 1),
                    new Order(1, 3),
                    new Order(2, 2),
                ),
            ],
            'brak zdjęć -> kolejność []' => [
                'recipePhotos' => [],
                'request' => new OrderList(),
            ],
        ];
    }

    public static function timersAndExpectedValues(): array
    {
        return [
            '1 timer bez nazwy, tylko czas' => [
                [['time' => 10]],
                new TimerList(...[new Timer(null, 10)]),
            ],
            '1 timer z nazwą i czasem' => [
                [['name' => 'Gotowanie', 'time' => 30]],
                new TimerList(...[new Timer('Gotowanie', 30)]),
            ],
            '2 timery z nazwą i czasem' => [
                [['name' => 'Gotowanie', 'time' => 30], ['name' => 'Pieczenie', 'time' => 45]],
                new TimerList(...[new Timer('Gotowanie', 30), new Timer('Pieczenie', 45)]),
            ],
            'pusta lista timerów' => [[], new TimerList(...[])],
        ];
    }

    public static function validBasicRecipeValues(): array
    {
        return [
            'Kanapka – minimalne dane (favourite = false, public = true, toDo = false)' => [
                new RecipeRequest(
                    'Kanapka',
                    null,
                    null,
                    3,
                    false,
                    true,
                    false,
                    new StringList(),
                    new TimerList(),
                    new RecipePositionsGroupList(),
                ),
            ],
            'Spaghetti – pełne dane (favourite = true, public = false, toDo = true)' => [
                new RecipeRequest(
                    'Spaghetti',
                    'Pyszne włoskie danie',
                    'https://example.com',
                    4,
                    true,
                    false,
                    true,
                    new StringList(),
                    new TimerList(),
                    new RecipePositionsGroupList(),
                ),
            ],
            'Zupa – pusty opis, z URL (favourite = true, public = true, toDo = true)' => [
                new RecipeRequest(
                    'Zupe',
                    '',
                    'https://example.com',
                    2,
                    true,
                    true,
                    true,
                    new StringList(),
                    new TimerList(),
                    new RecipePositionsGroupList(),
                ),
            ],
        ];
    }

    public static function validPositionsValues(): array
    {
        return [
            'dwie grupy: bez nazwy (2 pozycje - składnik i przepis) i "group3" (1 pozycja składnik)' => [
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
                                false,
                                null,
                                'ml',
                                null,
                                EntityFactory::getSimpleRecipe(2),
                            ),
                        ),
                    ),
                    new RecipePositionsGroup(
                        'group3',
                        new RecipePositionList(
                            new RecipePosition(
                                true,
                                2.0,
                                'szt',
                                EntityFactory::getSimpleIngredient(3),
                                null,
                            ),
                        ),
                    ),
                ),
                [
                    new RecipePositionGroup()->setName('')->addRecipePosition(
                        new RecipePositionEntity()->setAdditional(true)
                            ->setAmount(2.0)
                            ->setMeasure('szt')
                            ->setIngredient(EntityFactory::getSimpleIngredient(1))
                            ->setRecipe(null),
                    )
                        ->addRecipePosition(
                            new RecipePositionEntity()->setAdditional(false)
                                ->setAmount(null)
                                ->setMeasure('ml')
                                ->setRecipe(EntityFactory::getSimpleRecipe(2))
                                ->setIngredient(null),
                        ),
                    new RecipePositionGroup()->setName('group3')->addRecipePosition(
                        new RecipePositionEntity()->setAdditional(true)
                            ->setAmount(2.0)
                            ->setMeasure('szt')
                            ->setIngredient(EntityFactory::getSimpleIngredient(3))
                            ->setRecipe(null),
                    ),
                ],
            ],
            'jedna grupa "group1", bez pozycji' => [
                new RecipePositionsGroupList(new RecipePositionsGroup('group1', new RecipePositionList())),
                [new RecipePositionGroup()->setName('group1')],
            ],
            'jedna grupa "group2", 1 pozycja (składnik)' => [
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
                [
                    new RecipePositionGroup()->setName('group2')->addRecipePosition(
                        new RecipePositionEntity()->setAdditional(false)
                            ->setAmount(1.0)
                            ->setMeasure('szt')
                            ->setIngredient(EntityFactory::getSimpleIngredient(1))
                            ->setRecipe(null),
                    ),
                ],
            ],
            'jedna grupa bez nazwy, 2 pozycje (składnik i przepis)' => [
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
                                false,
                                1.0,
                                'ml',
                                null,
                                EntityFactory::getSimpleRecipe(2),
                            ),
                        ),
                    ),
                ),
                [
                    new RecipePositionGroup()->setName('')->addRecipePosition(
                        new RecipePositionEntity()->setAdditional(true)
                            ->setAmount(2.0)
                            ->setMeasure('szt')
                            ->setIngredient(EntityFactory::getSimpleIngredient(1))
                            ->setRecipe(null),
                    )
                        ->addRecipePosition(
                            new RecipePositionEntity()->setAdditional(false)
                                ->setAmount(1.0)
                                ->setMeasure('ml')
                                ->setIngredient(null)
                                ->setRecipe(EntityFactory::getSimpleRecipe(2)),
                        ),
                ],
            ],
        ];
    }

    public static function validTagsValues(): array
    {
        return [
            'brak istniejących -> dodaje [Obiad, Szybkie]' => [
                'existingTags' => [],
                'newTags' => ['Obiad', 'Szybkie'],
            ],
            'brak istniejących i brak nowych' => [
                'existingTags' => [],
                'newTags' => [],
            ],
            'istnieje [Obiad] -> dodaje [Deser, Szybkie]' => [
                'existingTags' => ['Obiad'],
                'newTags' => ['Deser', 'Szybkie'],
            ],
            'istnieją [Deser, Szybkie] -> dodaje [Obiad, Kolacja]' => [
                'existingTags' => ['Deser', 'Szybkie'],
                'newTags' => ['Obiad', 'Kolacja'],
            ],
        ];
    }

    public static function validTimerValues(): array
    {
        return [
            '1 timer [Gotowanie 1200s]' => [[new TimerRequest('Gotowanie', 1200)]],
            '2 timery [Smażenie 300s, Pieczenie 1800s]' => [
                [new TimerRequest('Smażenie', 300), new TimerRequest('Pieczenie', 1800)],
            ],
            'brak timerów' => [[]],
        ];
    }
}
