<?php

namespace App\Tests\Unit\Validation;

use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\PhotoValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(PhotoValidation::class)]
class PhotoValidationTest extends ValidationTestCase
{
    private PhotoValidation $photoValidation;

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validPhotoValues')]
    public function itAcceptsValidData(string $prefix, string $content): void
    {
        // Arrange
        $this->init(['photo' => $prefix . $content]);

        // Act
        $result = $this->photoValidation->validate();
        $dto = $this->photoValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
        $this->assertSame(base64_decode($content), $dto->getDecoded());
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
        $this->photoValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "photo"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyPhoto(mixed $photo): void
    {
        // Arrange
        $this->init(['photo' => $photo]);

        // Act
        $result = $this->photoValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[photo]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['photo' => 'valida photo value', 'extra_field' => 'value']);

        // Act
        $result = $this->photoValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Pole ze zdjęciem powinno być poprawnym base64')]
    #[DataProviderExternal(ValidationDataProvider::class, 'invalidPhotoValues')]
    public function itRejectsInvalidBase64(mixed $photo, string $errorMessage): void
    {
        // Arrange
        $this->init(['photo' => $photo]);

        // Act
        $result = $this->photoValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage($result, '[photo]', $errorMessage);
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "photo"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringPhoto(mixed $photo): void
    {
        // Arrange
        $this->init(['photo' => $photo]);

        // Act
        $result = $this->photoValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[photo]',
            ValidationErrors::TYPE_STRING,
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->photoValidation = new PhotoValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
        );
    }
}
