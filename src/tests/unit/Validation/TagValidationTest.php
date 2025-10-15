<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Repository\TagRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\TagValidation;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(TagValidation::class)]
#[CoversClass(UniqueNameForUser::class)]
class TagValidationTest extends ValidationTestCase
{
    private TagRepository $tagRepository;
    private TagValidation $tagValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepository = $this->createStub(TagRepository::class);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validTagValues')]
    public function itAcceptsValidData(string $name): void
    {
        // Arrange
        $this->init(['name' => $name]);

        // Act
        $result = $this->tagValidation->validate();
        $this->tagValidation->getDto();

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
        $this->tagValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "name"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyName(mixed $name): void
    {
        // Arrange
        $this->init(['name' => $name]);

        // Act
        $result = $this->tagValidation->validate();

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
        $this->init(['name' => 'Valid Name', 'extra_field' => 'value']);

        // Act
        $result = $this->tagValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "name"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringName(mixed $name): void
    {
        // Arrange
        $this->init(['name' => $name]);

        // Act
        $result = $this->tagValidation->validate();

        // Assert
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
        $this->init(['name' => str_repeat('a', LengthConfig::TAG + 1)]);

        // Act
        $result = $this->tagValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[name]',
            ValidationErrors::tooLong(LengthConfig::TAG),
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->tagValidation = new TagValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
            $this->tagRepository,
        );
    }
}
