<?php

namespace App\Tests\Unit\Validation\Recipe;

use App\Config\LengthConfig;
use App\Dto\List\Type\StringList;
use App\Dto\Request\List\RecipePositionsGroupList;
use App\Dto\Request\List\TimerList;
use App\Dto\Request\Recipe;
use App\Dto\Request\RecipePosition;
use App\Dto\Request\RecipePositionsGroup;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\RecipeDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\Recipe\PositionValidationHelper;
use App\Validation\Recipe\RecipeValidation;
use App\Validation\TimerValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(RecipeValidation::class)]
#[CoversClass(Recipe::class)]
#[CoversClass(RecipePositionsGroup::class)]
class RecipeValidationTest extends ValidationTestCase
{
    private IngredientRepository $ingredientRepository;
    private PositionValidationHelper $positionValidationHelper;
    private RecipeRepository $recipeRepository;
    private RecipeValidation $recipeValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ingredientRepository = $this->getMock(
            IngredientRepository::class,
            new AllowedMethod('findById', EntityFactory::getSimpleIngredient()),
        );
        $this->recipeRepository = $this->getMock(
            RecipeRepository::class,
            new AllowedMethod('findById', EntityFactory::getSimpleRecipe()),
        );
        $this->positionValidationHelper = new PositionValidationHelper(
            $this->ingredientRepository,
            $this->recipeRepository,
            $this->userService,
        );
    }

    #[Test]
    #[TestDox('Akceptuje pustą tablicę w polu "groups"')]
    public function itAcceptsEmptyGroups(): void
    {
        // Arrange
        $this->initWithDefaults(['groups' => []]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Akceptuje pustą tablicę w polu "tags"')]
    public function itAcceptsEmptyTags(): void
    {
        // Arrange
        $this->initWithDefaults(['tags' => []]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Akceptuje pustą tablicę w polu "times"')]
    public function itAcceptsEmptyTimers(): void
    {
        // Arrange
        $this->initWithDefaults(['timers' => []]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Tworzy listę grup na podstawie danych wejściowych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'recipeGroupsInputAndExpectedValues')]
    public function itCreatesGroupsListFromInput(array $request, RecipePositionsGroupList $dtoGroup): void
    {
        // Arrange
        $request['name'] = 'name';
        $request['favourite'] = false;
        $request['public'] = false;
        $request['toDo'] = false;
        $request['portions'] = 10;
        $this->request->method('toArray')->willReturn($request);
        $positionValidationHelper = $this->getMockBuilder(PositionValidationHelper::class)
            ->onlyMethods(['getDto'])
            ->setConstructorArgs([$this->ingredientRepository, $this->recipeRepository, $this->userService])
            ->getMock();
        $positionValidationHelper->method('getDto')->willReturnCallback(function (array $data): RecipePosition {
            return new RecipePosition(
                $data['additional'],
                $data['amount'] ?? null,
                $data['measure'],
                $data['ingredient'] === null ? null : EntityFactory::getSimpleIngredient($data['ingredient']),
                $data['recipe'] === null ? null : EntityFactory::getSimpleRecipe($data['recipe']),
            );
        });
        $timerValidation = new TimerValidation($this->validator, $this->requestStack, $this->userService);
        $recipeValidation = new RecipeValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
            $positionValidationHelper,
            $timerValidation,
        );

        // Act
        $result = $recipeValidation->validate();
        $dto = $recipeValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
        $this->assertEquals($dtoGroup, $dto->getGroups());
    }

    #[Test]
    #[TestDox('Tworzy listę timerów na podstawie danych wejściowych')]
    #[DataProviderExternal(RecipeDataProvider::class, 'timersAndExpectedValues')]
    public function itCreatesTimerListFromInput(array $timers, TimerList $expected): void
    {
        // Arrange
        $this->initWithDefaults(['timers' => $timers]);

        // Act
        $result = $this->recipeValidation->validate();
        $dto = $this->recipeValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
        $this->assertEquals($expected, $dto->getTimers());
    }

    #[Test]
    #[TestDox('Tablica z pozycjami nie może być pusta')]
    public function itRejectEmptyPositions(): void
    {
        // Arrange
        $this->initWithDefaults(['groups' => [['name' => 'name']]]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[groups][0][positions]',
            ValidationErrors::FIELD_MISSING,
        );
    }

    #[Test]
    #[TestDox('Nie pozwala pobrać DTO, gdy walidacja nie przeszła pomyślnie')]
    public function itRejectsDtoAccessWithoutValidation(): void
    {
        // Arrange
        $this->init([]);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ValidationErrors::ACCESS_DTO_BEFORE_VALIDATION);

        // Act
        $this->recipeValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "favourite"')]
    public function itRejectsEmptyFavourite(): void
    {
        // Arrange
        $this->initWithDefaults(['favourite' => null]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[favourite]',
            ValidationErrors::NOT_NULL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "name"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyName(mixed $name): void
    {
        // Arrange
        $this->initWithDefaults(['name' => $name]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "portions"')]
    public function itRejectsEmptyPortions(): void
    {
        // Arrange
        $this->initWithDefaults(['portions' => null]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[portions]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "public"')]
    public function itRejectsEmptyPublic(): void
    {
        // Arrange
        $this->initWithDefaults(['public' => null]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[public]',
            ValidationErrors::NOT_NULL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste wartości w polu "tags"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyTag(mixed $tag): void
    {
        // Arrange
        $this->initWithDefaults(['tags' => [$tag]]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[tags][0]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "toDo"')]
    public function itRejectsEmptyToDo(): void
    {
        // Arrange
        $this->initWithDefaults(['toDo' => null]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[toDo]',
            ValidationErrors::NOT_NULL,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->initWithDefaults(['extra_field' => 'value']);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola - w polu "groups"')]
    public function itRejectsExtraFieldsInGroups(): void
    {
        // Arrange
        $this->initWithDefaults([
            'groups' => [
                [
                    'extra_field' => 'value',
                    'name' => 'name',
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
        ]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[groups][0][extra_field]',
            ValidationErrors::UNEXPECTED_FIELD,
        );
    }

    #[Test]
    #[TestDox('Odrzuca błędne dane w polu "positions"')]
    public function itRejectsInvalidPositions(): void
    {
        // Arrange
        $this->initWithDefaults([
            'groups' => [['name' => 'name', 'positions' => ['ingredient' => 1]]],
        ]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertCount(1, $result->getErrors());
    }

    #[Test]
    #[TestDox('Odrzuca błędne dane w polu "timers"')]
    public function itRejectsInvalidTimers(): void
    {
        // Arrange
        $this->initWithDefaults(['timers' => [[]]]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertCount(1, $result->getErrors());
    }

    #[Test]
    #[TestDox('Odrzuca błędny adres url w polu "url"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidUrls')]
    public function itRejectsInvalidUrl(mixed $url): void
    {
        // Arrange
        $this->initWithDefaults(['url' => $url]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage($result, '[url]', null);
    }

    #[Test]
    #[TestDox('Odrzuca nie-tablice w polu "groups"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankArrayValues')]
    public function itRejectsNonArrayGroups(mixed $groups): void
    {
        // Arrange
        $this->initWithDefaults(['groups' => $groups]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[groups]',
            ValidationErrors::TYPE_ITERABLE,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-tablice w polu "positions"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankArrayValues')]
    public function itRejectsNonArrayPositions(mixed $positions): void
    {
        // Arrange
        $this->initWithDefaults(['groups' => [['positions' => $positions]]]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[groups][0][positions]',
            ValidationErrors::TYPE_ARRAY,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-tablice w polu "tags"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankArrayValues')]
    public function itRejectsNonArrayTags(mixed $tags): void
    {
        // Arrange
        $this->initWithDefaults(['tags' => $tags]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[tags]',
            ValidationErrors::TYPE_ITERABLE,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-tablice w polu "timers"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankArrayValues')]
    public function itRejectsNonArrayTimers(mixed $timers): void
    {
        // Arrange
        $this->initWithDefaults(['timers' => $timers]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[timers]',
            ValidationErrors::TYPE_ITERABLE,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-boole w polu "favourite"')]
    #[DataProviderExternal(CommonDataProvider::class, 'notBoolValuesExpectNull')]
    public function itRejectsNonBoolFavourite(mixed $value): void
    {
        // Arrange
        $this->initWithDefaults(['favourite' => $value]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[favourite]',
            ValidationErrors::TYPE_BOOL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-boole w polu "public"')]
    #[DataProviderExternal(CommonDataProvider::class, 'notBoolValuesExpectNull')]
    public function itRejectsNonBoolPublic(mixed $value): void
    {
        // Arrange
        $this->initWithDefaults(['public' => $value]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[public]',
            ValidationErrors::TYPE_BOOL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-boole w polu "toDo"')]
    #[DataProviderExternal(CommonDataProvider::class, 'notBoolValuesExpectNull')]
    public function itRejectsNonBoolToDo(mixed $value): void
    {
        // Arrange
        $this->initWithDefaults(['toDo' => $value]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[toDo]',
            ValidationErrors::TYPE_BOOL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "portions"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntPortions(mixed $portions): void
    {
        // Arrange
        $this->initWithDefaults(['portions' => $portions]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[portions]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca niedodatnie wartości w polu "portions')]
    #[DataProviderExternal(CommonDataProvider::class, 'lessThanOrEqualZero')]
    public function itRejectsNonPositivePortions(int $portions): void
    {
        // Arrange
        $this->initWithDefaults(['portions' => $portions]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[portions]',
            ValidationErrors::shouldBeGreaterThan(0),
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "description"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringDescription(mixed $description): void
    {
        // Arrange
        $this->initWithDefaults(['description' => $description]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[description]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w nazwie grupy')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringGroupName(mixed $name): void
    {
        // Arrange
        $this->initWithDefaults([
            'groups' => [
                [
                    'name' => $name,
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
        ]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[groups][0][name]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "name"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringName(mixed $name): void
    {
        // Arrange
        $this->initWithDefaults(['name' => $name]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "tags"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringTag(mixed $tag): void
    {
        // Arrange
        $this->initWithDefaults(['tags' => [$tag]]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[tags][0]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "url"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringUrl(mixed $url): void
    {
        // Arrange
        $this->initWithDefaults(['url' => [$url]]);

        // Act
        $result = $this->recipeValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[url]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "groups.name"')]
    public function itRejectsTooLongGroupName(): void
    {
        // Arrange
        $this->initWithDefaults([
            'groups' => [
                [
                    'name' => str_repeat('a', 256),
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
        ]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[groups][0][name]',
            ValidationErrors::tooLong(255),
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "name"')]
    public function itRejectsTooLongName(): void
    {
        // Arrange
        $this->initWithDefaults(['name' => str_repeat('a', LengthConfig::RECIPE + 1)]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::tooLong(LengthConfig::RECIPE),
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długie wartości w polu "tags"')]
    public function itRejectsTooLongTag(): void
    {
        // Arrange
        $this->initWithDefaults(['tags' => [str_repeat('a', LengthConfig::TAG + 1)]]);

        // Act
        $result = $this->recipeValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[tags][0]',
            ValidationErrors::tooLong(LengthConfig::TAG),
        );
    }

    #[Test]
    #[TestDox('Zwraca unikalne tagi zapisane wielkimi literami')]
    #[DataProviderExternal(ValidationDataProvider::class, 'uniqueUppercaseTagsValues')]
    public function itReturnsUniqueUppercaseTags(array $tags, StringList $expected): void
    {
        // Arrange
        $this->initWithDefaults(['tags' => $tags]);

        // Act
        $result = $this->recipeValidation->validate();
        $dto = $this->recipeValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
        $this->assertEquals($expected, $dto->getTags());
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $timerValidation = new TimerValidation($this->validator, $this->requestStack, $this->userService);
        $this->recipeValidation = new RecipeValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
            $this->positionValidationHelper,
            $timerValidation,
        );
    }

    private function initWithDefaults(array $overrides = []): void
    {
        $defaults = [
            'favourite' => false,
            'name' => 'name',
            'portions' => 10,
            'public' => false,
            'toDo' => false,
        ];
        $this->init(array_merge($defaults, $overrides));
    }
}
