<?php

namespace App\Tests\Unit\Validation;

use App\Dto\Request\EditSeason;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\EditSeasonValidation;
use App\ValidationPolicy\CorrectMonth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(EditSeasonValidation::class)]
#[CoversClass(EditSeason::class)]
#[CoversClass(CorrectMonth::class)]
class EditSeasonValidationTest extends ValidationTestCase
{
    private EditSeasonValidation $editSeasonValidation;

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validEditSeasonValues')]
    public function itAcceptsValidData(int $start, int $stop): void
    {
        // Arrange
        $this->init(['start' => $start, 'stop' => $stop]);

        // Act
        $result = $this->editSeasonValidation->validate();
        $this->editSeasonValidation->getDto();

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
        $this->editSeasonValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "start"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyStart(mixed $start): void
    {
        // Arrange
        $this->init(['start' => $start, 'stop' => 2]);

        // Act
        $result = $this->editSeasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[start]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "stop"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyStop(mixed $stop): void
    {
        // Arrange
        $this->init(['start' => 2, 'stop' => $stop]);

        // Act
        $result = $this->editSeasonValidation->validate();

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
        $this->init(['start' => 1, 'stop' => 2, 'extra_field' => 'value']);

        // Act
        $result = $this->editSeasonValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "start"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntStart(mixed $start): void
    {
        // Arrange
        $this->init(['start' => $start, 'stop' => 2]);

        // Act
        $result = $this->editSeasonValidation->validate();

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
        $this->init(['start' => 2, 'stop' => $stop]);

        // Act
        $result = $this->editSeasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[stop]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Pole "start" powinno zawierać wartości z zakresu <1,12>')]
    #[DataProviderExternal(ValidationDataProvider::class, 'outOfRangeSeasonValues')]
    public function itRejectsStartOutOfRange(int $start): void
    {
        // Arrange
        $this->init(['start' => $start, 'stop' => 12]);

        // Act
        $result = $this->editSeasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage($result, '[start]', ValidationErrors::shouldBeBetween(1, 12));
    }

    #[Test]
    #[TestDox('Wartość w polu "stop" musi być większa niż wartość w polu "start"')]
    #[DataProviderExternal(ValidationDataProvider::class, 'invalidEditSeasonValues')]
    public function itRejectsStopLessOrEqualThanStart(int $start, int $stop): void
    {
        // Arrange
        $this->init(['start' => $start, 'stop' => $stop]);

        // Act
        $result = $this->editSeasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[stop]',
            ValidationErrors::STOP_MUST_BE_GREATER_THAN_START,
        );
    }

    #[Test]
    #[TestDox('Pole "stop" powinno zawierać wartości z zakresu <1,12>')]
    #[DataProviderExternal(ValidationDataProvider::class, 'outOfRangeSeasonValues')]
    public function itRejectsStopOutOfRange(int $stop): void
    {
        // Arrange
        $this->init(['start' => 1, 'stop' => $stop]);

        // Act
        $result = $this->editSeasonValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage($result, '[stop]', ValidationErrors::shouldBeBetween(1, 12));
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->editSeasonValidation = new EditSeasonValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
        );
    }
}
