<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Repository\IngredientRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\CreateIngredientValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(CreateIngredientValidation::class)]
class CreateIngredientValidationTest extends ValidationTestCase
{
    private CreateIngredientValidation $createIngredientValidation;
    private IngredientRepository $ingredientRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ingredientRepository = $this->createStub(IngredientRepository::class);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validCreateIngredientValues')]
    public function itAcceptsValidData(array $data): void
    {
        // Arrange
        $this->init($data);

        // Act
        $result = $this->createIngredientValidation->validate();
        $this->createIngredientValidation->getDto();

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
        $this->createIngredientValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "name"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyName(mixed $name): void
    {
        // Arrange
        $this->initWithDefaults(['name' => $name]);

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->initWithDefaults(['extra_field' => 'value']);

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-boole w polu "available"')]
    #[DataProviderExternal(CommonDataProvider::class, 'notBoolValuesExpectNull')]
    public function itRejectsNonBoolAvailable(mixed $value): void
    {
        // Arrange
        $this->initWithDefaults(['available' => $value]);

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[available]',
            ValidationErrors::TYPE_BOOL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "ozaId"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntOzaId(mixed $value): void
    {
        // Arrange
        $this->initWithDefaults(['ozaId' => $value]);

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[ozaId]',
            ValidationErrors::TYPE_INT,
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
        $result = $this->createIngredientValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "name"')]
    public function itRejectsTooLongName(): void
    {
        // Arrange
        $this->initWithDefaults(['name' => str_repeat('a', LengthConfig::INGREDIENT + 1)]);

        // Act
        $result = $this->createIngredientValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::tooLong(LengthConfig::INGREDIENT),
        );
    }

    #[Test]
    #[TestDox('Pole "available" musi być przesłane')]
    public function itTriggersErrorWhenAvailableIsMissing(): void
    {
        // Arrange
        $this->init(['name' => 'valid name', 'ozaId' => 1]);

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[available]',
            ValidationErrors::FIELD_MISSING,
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->createIngredientValidation = new CreateIngredientValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
            $this->ingredientRepository,
        );
    }

    private function initWithDefaults(array $overrides = []): void
    {
        $defaults = [
            'available' => true,
            'name' => 'valid name',
            'ozaId' => 1,
        ];
        $this->init(array_merge($defaults, $overrides));
    }
}
