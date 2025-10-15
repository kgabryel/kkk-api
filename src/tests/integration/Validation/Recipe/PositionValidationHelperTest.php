<?php

namespace App\Tests\Integration\Validation\Recipe;

use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Service\UserService;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\Recipe\PositionValidationHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(PositionValidationHelper::class)]
class PositionValidationHelperTest extends BaseIntegrationTestCase
{
    private PositionValidationHelper $helper;
    private Sequentially $rules;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $this->validator = Validation::createValidator();
        $ingredientRepository = $container->get(IngredientRepository::class);
        $recipeRepository = $container->get(RecipeRepository::class);
        $userService = $this->createStub(UserService::class);
        $userService->method('getUser')->willReturn($this->defaultUser);
        $this->helper = new PositionValidationHelper(
            $ingredientRepository,
            $recipeRepository,
            $userService,
        );
        $this->rules = $this->helper->getRules();
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych - przesłana wartość dla pola "ingredient"')]
    public function itAcceptsValidDataWithIngredientSet(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient($this->defaultUser->getEmail());
        $data = $this->getData(['ingredient' => $ingredient->getId()]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $dto = $this->helper->getDto($data);

        // Assert
        $this->assertHasNoViolations($violations);
        $this->assertNull($dto->getRecipe()?->getId());
        $this->assertSame($ingredient->getId(), $dto->getIngredient()->getId());
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych - przesłana wartość dla pola "recipe"')]
    public function itAcceptsValidDataWithRecipeSet(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe($this->defaultUser->getEmail());
        $data = $this->getData(['recipe' => $recipe->getId()]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Prepare expected
        $dto = $this->helper->getDto($data);

        // Assert
        $this->assertHasNoViolations($violations);
        $this->assertNull($dto->getIngredient()?->getId());
        $this->assertSame($recipe->getId(), $dto->getRecipe()->getId());
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy składnik nie istnieje')]
    public function itFailsWhenWhenIngredientNotExists(): void
    {
        // Arrange
        $data = $this->getData(['ingredient' => 2]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $violations,
            'No matching item found for this user.',
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy składnik nie należy do użytkownika')]
    public function itFailsWhenWhenIngredientUnavailable(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient(EntityFactory::USER_EMAIL_2);
        $data = $this->getData(['ingredient' => $ingredient->getId()]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $violations,
            'No matching item found for this user.',
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy przepis nie istnieje')]
    public function itFailsWhenWhenRecipeNotExists(): void
    {
        // Arrange
        $data = $this->getData(['recipe' => 2]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $violations,
            'No matching item found for this user.',
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy przepis nie należy do użytkownika')]
    public function itFailsWhenWhenRecipeUnavailable(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe(EntityFactory::USER_EMAIL_2);
        $data = $this->getData(['recipe' => $recipe->getId()]);

        // Act
        $violations = $this->validator->validate($data, $this->rules);

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $violations,
            'No matching item found for this user.',
        );
    }

    private function getData(array $overrides = []): array
    {
        $defaults = ['additional' => false, 'amount' => 10.0, 'measure' => 'kg'];

        return array_merge($defaults, $overrides);
    }
}
