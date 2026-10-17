<?php

namespace App\Tests\Unit\Validation;

use App\Entity\Recipe;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\ReorderPhotosValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(ReorderPhotosValidation::class)]
class ReorderPhotosValidationTest extends ValidationTestCase
{
    private Recipe $recipe;
    private ReorderPhotosValidation $reorderPhotosValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recipe = EntityFactory::getSimpleRecipe();
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    public function itAcceptsValidData(): void
    {
        // Arrange
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        $this->request->method('toArray')->willReturn(['order' => [['id' => 1, 'index' => 1]]]);
        $reorderPhotosValidation = new ReorderPhotosValidation($this->validator, $this->requestStack);

        // Act
        $result = $reorderPhotosValidation->validate($this->recipe);
        $reorderPhotosValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Nie pozwala pobrać DTO, gdy walidacja nie przeszła pomyślnie')]
    public function itRejectsDtoAccessWithoutValidation(): void
    {
        // Arrange
        $reorderPhotosValidation = new ReorderPhotosValidation($this->validator, $this->requestStack);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ValidationErrors::ACCESS_DTO_BEFORE_VALIDATION);

        // Act
        $reorderPhotosValidation->getDto();
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['order' => [['id' => 1, 'index' => 1]], 'extra_field' => 'value']);

        // Act
        $result = $this->reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola - w polu "order"')]
    public function itRejectsExtraFieldsInOrder(): void
    {
        // Arrange
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        $this->init(['order' => [['id' => 1, 'index' => 1, 'extra_field' => 'value']]]);
        $reorderPhotosValidation = new ReorderPhotosValidation($this->validator, $this->requestStack);

        // Act
        $result = $reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[order][0][extra_field]',
            ValidationErrors::UNEXPECTED_FIELD,
        );
    }

    #[Test]
    #[TestDox('Odrzuca ID, które nie znajduje się na liście dozwolonych')]
    public function itRejectsIdOutsideAllowedChoices(): void
    {
        // Arrange
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto(2));
        $this->init(
            [
                'order' => [
                    ['id' => 999, 'index' => 1],
                    ['id' => 1, 'index' => 2],
                ],
            ],
        );

        // Act
        $result = $this->reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[order][0][id]',
            ValidationErrors::INVALID_CHOICE,
        );
    }

    #[Test]
    #[TestDox('Odrzuca niepoprawną ilość zdjęć')]
    #[DataProviderExternal(ValidationDataProvider::class, 'invalidReorderPhotosCountValues')]
    public function itRejectsInvalidCount(int $recipePhotosCount, int $requestCount): void
    {
        // Arrange
        for ($i = 0; $i < $recipePhotosCount; ++$i) {
            $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        }
        $request = ['order' => []];
        for ($i = 0; $i < $requestCount; ++$i) {
            $request['order'][] = ['index' => $i, 'id' => $i + 1];
        }
        $this->init($request);

        // Act
        $result = $this->reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[order]',
            ValidationErrors::invalidCount($recipePhotosCount),
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "id"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntId(mixed $value): void
    {
        // Arrange
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        $this->init(['order' => [['id' => $value, 'index' => 1]]]);

        // Act
        $result = $this->reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[order][0][id]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "index"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntIndex(mixed $value): void
    {
        // Arrange
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        $this->init(['order' => [['id' => 1, 'index' => $value]]]);

        // Act
        $result = $this->reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[order][0][index]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca ID jeżeli zostało przesłane wielokrotnie')]
    public function itRejectsNonUniqueIds(): void
    {
        // Arrange
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto(2));
        $this->init(
            [
                'order' => [
                    ['id' => 1, 'index' => 1],
                    ['id' => 1, 'index' => 2],
                ],
            ],
        );

        // Act
        $result = $this->reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[order]',
            ValidationErrors::nonUniqueValue('id'),
        );
    }

    #[Test]
    #[TestDox('Odrzuca Index jeżeli został przesłany wielokrotnie')]
    public function itRejectsNonUniqueIndexes(): void
    {
        // Arrange
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto());
        $this->recipe->addPhoto(EntityFactory::getSimplePhoto(2));
        $this->init(
            [
                'order' => [
                    ['id' => 1, 'index' => 1],
                    ['id' => 2, 'index' => 1],
                ],
            ],
        );

        // Act
        $result = $this->reorderPhotosValidation->validate($this->recipe);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[order]',
            ValidationErrors::nonUniqueValue('index'),
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->reorderPhotosValidation = new ReorderPhotosValidation($this->validator, $this->requestStack);
    }
}
