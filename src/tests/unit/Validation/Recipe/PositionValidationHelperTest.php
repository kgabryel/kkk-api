<?php

namespace App\Tests\Unit\Validation\Recipe;

use App\Dto\Request\RecipePosition;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\Recipe\PositionValidationHelper;
use App\Validation\Result;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Validation;

#[Small]
#[CoversClass(PositionValidationHelper::class)]
#[CoversClass(RecipePosition::class)]
class PositionValidationHelperTest extends ValidationTestCase
{
    private PositionValidationHelper $helper;
    private Ingredient $ingredient;
    private Recipe $recipe;
    private Sequentially $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recipe = EntityFactory::getSimpleRecipe();
        $this->ingredient = EntityFactory::getSimpleIngredient();
        $this->validator = Validation::createValidator();
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validIngredientRecipePairValues')]
    public function itAcceptsValidData(?int $ingredient, ?int $recipe): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', EntityFactory::getSimpleIngredient($ingredient ?? 999)),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', EntityFactory::getSimpleRecipe($recipe ?? 999)),
            ),
        );
        $data = $this->getData(['ingredient' => $ingredient, 'recipe' => $recipe]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);
        $dto = $this->helper->getDto($data);

        // Assert
        $this->assertTrue($result->passed());
        $this->assertSame($ingredient, $dto->getIngredient()?->getId());
        $this->assertSame($recipe, $dto->getRecipe()?->getId());
    }

    #[Test]
    #[TestDox('Odrzuca dane, gdy oba pola "ingredient" i "recipe" są wypełnione lub oba puste')]
    #[DataProviderExternal(ValidationDataProvider::class, 'invalidIngredientRecipePairValues')]
    public function itFailsWhenIngredientRecipePairInvalid(?int $ingredient, ?int $recipe): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['ingredient' => $ingredient, 'recipe' => $recipe]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '',
            ValidationErrors::EXACTLY_ONE_OF_INGREDIENT_OR_RECIPE,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "additional"')]
    public function itRejectsEmptyAdditional(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['additional' => null, 'ingredient' => 1]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[additional]',
            ValidationErrors::NOT_NULL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "measure"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyMeasure(mixed $measure): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['measure' => $measure, 'ingredient' => 1]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[measure]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(
            $this->createStub(IngredientRepository::class),
            $this->createStub(RecipeRepository::class),
        );
        $data = $this->getData(['ingredient' => 1, 'extra_field' => 'value']);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-boole w polu "additional"')]
    #[DataProviderExternal(CommonDataProvider::class, 'notBoolValuesExpectNull')]
    public function itRejectsNonBoolAdditional(mixed $value): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['additional' => $value, 'ingredient' => 1]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[additional]',
            ValidationErrors::TYPE_BOOL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-floaty w polu "amount"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankNumberValues')]
    public function itRejectsNonFloatAmount(mixed $amount): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['amount' => $amount, 'ingredient' => 1]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[amount]',
            ValidationErrors::TYPE_NUMBER,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "ingredient"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntIngredient(mixed $ingredient): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
        );
        $data = $this->getData(['ingredient' => $ingredient]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[ingredient]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-inty w polu "recipe"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankIntegerValues')]
    public function itRejectsNonIntRecipe(mixed $recipe): void
    {
        // Arrange
        $this->init(
            recipeRepository: $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['recipe' => $recipe]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[recipe]',
            ValidationErrors::TYPE_INT,
        );
    }

    #[Test]
    #[TestDox('Odrzuca wartości mniejsze lub równe zero w polu "amount"')]
    #[DataProviderExternal(CommonDataProvider::class, 'lessThanOrEqualZeroFloat')]
    public function itRejectsNonPositiveAmount(mixed $amount): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['amount' => $amount, 'ingredient' => 1]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[amount]',
            ValidationErrors::shouldBeGreaterThan(0),
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "measure"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringMeasure(mixed $measure): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['measure' => $measure, 'ingredient' => 1]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[measure]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "measure"')]
    public function itRejectsTooLongMeasure(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientRepository::class,
                new AllowedMethod('findById', $this->ingredient),
            ),
            $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $this->recipe),
            ),
        );
        $data = $this->getData(['ingredient' => 123, 'measure' => str_repeat('a', 101)]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $result = new Result($violations);

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage($result, '[measure]', ValidationErrors::tooLong(100));
    }

    private function getData(array $overrides = []): array
    {
        $defaults = ['additional' => false, 'amount' => 10.0, 'measure' => 'kg'];

        return array_merge($defaults, $overrides);
    }

    private function init(
        ?IngredientRepository $ingredientRepository = null,
        ?RecipeRepository $recipeRepository = null,
    ): void {
        $this->helper = new PositionValidationHelper(
            $ingredientRepository ?? $this->getMock(IngredientRepository::class),
            $recipeRepository ?? $this->getMock(RecipeRepository::class),
            $this->userService,
        );
        $this->rules = $this->helper->getRules();
    }
}
