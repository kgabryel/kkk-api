<?php

namespace App\Tests\Unit\Validation;

use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\RecipeDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\RecipeFlagsValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(RecipeFlagsValidation::class)]
class RecipeFlagsValidationTest extends ValidationTestCase
{
    private RecipeFlagsValidation $recipeFlagsValidation;

    #[Test]
    #[TestDox('Przesłane dane mogą być puste')]
    public function itAcceptsEmptyPayload(): void
    {
        // Arrange
        $this->init([]);

        // Act
        $result = $this->recipeFlagsValidation->validate();
        $this->recipeFlagsValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(RecipeDataProvider::class, 'flagValues')]
    public function itAcceptsValidData(?bool $favourite, ?bool $toDo): void
    {
        // Arrange
        $this->init(['favourite' => $favourite, 'toDo' => $toDo]);

        // Act
        $result = $this->recipeFlagsValidation->validate();
        $this->recipeFlagsValidation->getDto();

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
        $this->recipeFlagsValidation->getDto();
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['extra_field' => 'value']);

        // Act
        $result = $this->recipeFlagsValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-boole w polu "favourite"')]
    #[DataProviderExternal(CommonDataProvider::class, 'notBoolValuesExpectNull')]
    public function itRejectsNonBoolFavourite(mixed $value): void
    {
        // Arrange
        $this->init(['favourite' => $value]);

        // Act
        $result = $this->recipeFlagsValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[favourite]',
            ValidationErrors::TYPE_BOOL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-boole w polu "toDo"')]
    #[DataProviderExternal(CommonDataProvider::class, 'notBoolValuesExpectNull')]
    public function itRejectsNonBoolToDo(mixed $value): void
    {
        // Arrange
        $this->init(['toDo' => $value]);

        // Act
        $result = $this->recipeFlagsValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[toDo]',
            ValidationErrors::TYPE_BOOL,
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->recipeFlagsValidation = new RecipeFlagsValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
        );
    }
}
