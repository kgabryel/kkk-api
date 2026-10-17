<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\TimerValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(TimerValidation::class)]
class TimerValidationTest extends ValidationTestCase
{
    private TimerValidation $timerValidation;

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validTimerValues')]
    public function itAcceptsValidData(?string $name, int $time): void
    {
        // Arrange
        $this->init(['name' => $name, 'time' => $time]);

        // Act
        $result = $this->timerValidation->validate();
        $this->timerValidation->getDto();

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
        $this->timerValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "time"')]
    public function itRejectsEmptyTime(): void
    {
        // Arrange
        $this->init(['time' => null]);

        // Act
        $result = $this->timerValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[time]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['name' => null, 'time' => 1, 'extra_field' => 'value']);

        // Act
        $result = $this->timerValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "time"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntTime(mixed $time): void
    {
        $this->init(['name' => 'name', 'time' => $time]);

        // Act
        $result = $this->timerValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[time]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca niedodatnie wartości w polu "time')]
    #[DataProviderExternal(CommonDataProvider::class, 'lessThanOrEqualZero')]
    public function itRejectsNonPositiveTime(int $time): void
    {
        // Arrange
        $this->init(['time' => $time]);

        // Act
        $result = $this->timerValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[time]',
            ValidationErrors::VALUE_SHOULD_BE_POSITIVE,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "name"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringName(mixed $name): void
    {
        // Arrange
        $this->init(['name' => $name, 'time' => 1]);

        // Act
        $result = $this->timerValidation->validate();

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
        $this->init(['name' => str_repeat('a', LengthConfig::TIMER + 1), 'time' => 1]);

        // Act
        $result = $this->timerValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::tooLong(LengthConfig::TIMER),
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->timerValidation = new TimerValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
        );
    }
}
