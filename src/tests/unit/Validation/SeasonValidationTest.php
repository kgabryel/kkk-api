<?php

namespace App\Tests\Unit\Validation;

use App\Dto\Request\Season;
use App\Repository\IngredientRepository;
use App\Repository\SeasonRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\SeasonValidation;
use App\ValidatorRule\ExistsForUser\ExistsForUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(SeasonValidation::class)]
#[CoversClass(Season::class)]
#[CoversClass(ExistsForUser::class)]
class SeasonValidationTest extends ValidationTestCase
{
    private IngredientRepository $ingredientRepository;
    private SeasonRepository $seasonRepository;
    private SeasonValidation $seasonValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ingredientRepository = $this->createStub(IngredientRepository::class);
        $this->ingredientRepository->method('findById')->willReturn(EntityFactory::getSimpleIngredient());
        $this->seasonRepository = $this->createStub(SeasonRepository::class);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validCreateSeasonValues')]
    public function itAcceptsValidData(int $start, int $stop, int $ingredientId): void
    {
        // Arrange
        $ingredient = EntityFactory::getSimpleIngredient($ingredientId);
        $ingredientRepository = $this->getMock(
            IngredientRepository::class,
            new AllowedMethod('findById', $ingredient, parameters: [$ingredientId]),
        );
        $this->init(['start' => $start, 'stop' => $stop, 'ingredient' => $ingredientId], $ingredientRepository);

        // Act
        $result = $this->seasonValidation->validate();
        $this->seasonValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
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
        $this->seasonValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "ingredient"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyIngredient(mixed $ingredient): void
    {
        // Arrange
        $this->initWithDefaults(
            ['ingredient' => $ingredient],
            $this->createStub(IngredientRepository::class),
        );

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[ingredient]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "start"')]
    public function itRejectsEmptyStart(): void
    {
        // Arrange
        $this->initWithDefaults(['start' => null]);

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[start]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "stop"')]
    public function itRejectsEmptyStop(): void
    {
        // Arrange
        $this->initWithDefaults(['stop' => null]);

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[stop]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->initWithDefaults(
            ['extra_field' => 'value'],
            $this->createStub(IngredientRepository::class),
        );

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "ingredient"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntIngredient(mixed $ingredient): void
    {
        // Arrange
        $this->initWithDefaults(
            ['ingredient' => $ingredient],
            $this->createStub(IngredientRepository::class),
        );

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[ingredient]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "start"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntStart(mixed $start): void
    {
        // Arrange
        $this->initWithDefaults(['start' => $start]);

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[start]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "stop"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntStop(mixed $stop): void
    {
        // Arrange
        $this->initWithDefaults(['stop' => $stop]);

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[stop]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('"Start" musi być w zakresie 1–12')]
    #[DataProviderExternal(ValidationDataProvider::class, 'outOfRangeSeasonValues')]
    public function startShouldBeInValidRange(int $start): void
    {
        // Arrange
        $this->initWithDefaults(['start' => $start]);

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage($result, '[start]', ValidationErrors::shouldBeBetween(1, 12));
    }

    #[Test]
    #[TestDox('"Stop" musi być większe niż "start"')]
    public function stopMustBeGreaterThanStart(): void
    {
        // Arrange
        $this->init(['start' => 12, 'stop' => 3, 'ingredient' => 1]);

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[stop]',
            ValidationErrors::STOP_MUST_BE_GREATER_THAN_START,
        );
    }

    #[Test]
    #[TestDox('"Stop" musi być w zakresie 1–12')]
    #[DataProviderExternal(ValidationDataProvider::class, 'outOfRangeSeasonValues')]
    public function stopShouldBeInValidRange(int $stop): void
    {
        // Arrange
        $this->init(['start' => 1, 'stop' => $stop, 'ingredient' => 1]);

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage($result, '[stop]', ValidationErrors::shouldBeBetween(1, 12));
    }

    private function init(array $requestData, ?IngredientRepository $ingredientRepository = null): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->seasonValidation = new SeasonValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
            $ingredientRepository ?? $this->ingredientRepository,
            $this->seasonRepository,
        );
    }

    private function initWithDefaults(array $overrides = [], ?IngredientRepository $ingredientRepository = null): void
    {
        $defaults = [
            'ingredient' => 1,
            'start' => 1,
            'stop' => 12,
        ];
        $this->init(array_merge($defaults, $overrides), $ingredientRepository);
    }
}
