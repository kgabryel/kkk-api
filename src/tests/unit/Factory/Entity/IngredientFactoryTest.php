<?php

namespace App\Tests\Unit\Factory\Entity;

use App\Dto\Request\CreateIngredient;
use App\Entity\Ingredient;
use App\Entity\User;
use App\Factory\Entity\IngredientFactory;
use App\Service\UserService;
use App\Tests\DataProvider\EntityFactoryDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\CreateIngredientValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(IngredientFactory::class)]
#[CoversClass(Ingredient::class)]
#[CoversClass(CreateIngredient::class)]
class IngredientFactoryTest extends BaseTestCase
{
    private User $user;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
    }

    #[Test]
    #[TestDox('Tworzy encję (Ingredient), gdy walidacja przeszła pomyślnie')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'ingredientDataValues')]
    public function itCreatesIngredientOnValidInput(string $ingredientName, bool $isAvailable, ?int $ozaId): void
    {
        // Arrange
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod(
                'persist',
                $this->once(),
                [$this->callback($this->ingredientMatcher($ingredientName, $isAvailable, $ozaId))],
            ),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $factory = new IngredientFactory($entityManager, $this->userService);
        $ingredientModel = new CreateIngredient($ingredientName, $isAvailable, $ozaId);
        $ingredientValidation = $this->getMock(
            CreateIngredientValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $ingredientModel, $this->once()),
        );

        // Act
        $ingredient = $factory->create($ingredientValidation);

        // Assert
        $this->assertInstanceOf(Ingredient::class, $ingredient);
    }

    #[Test]
    #[TestDox('Odrzuca dane i przerywa tworzenie, gdy walidacja się nie powiodła')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $factory = new IngredientFactory($this->getMock(EntityManagerInterface::class), $this->userService);
        $ingredientValidation = $this->getMock(
            CreateIngredientValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $ingredient = $factory->create($ingredientValidation);

        // Assert
        $this->assertNull($ingredient);
    }

    private function ingredientMatcher(string $ingredientName, bool $isAvailable, ?int $ozaId): callable
    {
        $user = $this->user;

        return static function ($ingredient) use ($user, $ingredientName, $isAvailable, $ozaId): bool {
            return $ingredient instanceof Ingredient
                && $ingredient->getName() === $ingredientName
                && $ingredient->isAvailable() === $isAvailable
                && $ingredient->getOzaId() === $ozaId
                && $ingredient->getUser() === $user;
        };
    }
}
