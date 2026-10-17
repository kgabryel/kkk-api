<?php

namespace App\Tests\Unit\Service\Entity;

use App\Dto\Request\EditIngredient;
use App\Entity\Ingredient;
use App\Entity\Ingredient as IngredientEntity;
use App\Entity\User;
use App\Repository\IngredientRepository;
use App\Service\Entity\IngredientService;
use App\Service\UserService;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\FindEntityDataProvider;
use App\Tests\DataProvider\UpdateEntityDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\EditIngredientValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(IngredientService::class)]
#[CoversClass(EditIngredient::class)]
class IngredientServiceTest extends BaseTestCase
{
    private Ingredient $ingredient;
    private IngredientService $ingredientService;
    private User $user;

    protected function setUp(): void
    {
        $this->ingredient = EntityFactory::getSimpleIngredient();
        $this->user = EntityFactory::getSimpleUser();
    }

    #[Test]
    #[TestDox('Resetuje OzaId dla użytkownika')]
    public function itClearsOzaKeys(): void
    {
        // Arrange
        $this->init(
            ingredientRepository: $this->getMock(
                IngredientRepository::class,
                new AllowedVoidMethod('resetOzaIdsForUser', $this->once(), [$this->user]),
            ),
        );

        // Act
        $this->ingredientService->clearOzaKeys();
    }

    #[Test]
    #[TestDox('Czyści OzaId i zapisuje zmiany')]
    public function itDisconnectsFromOza(): void
    {
        // Arrange
        $this->ingredient->setOzaId(11);
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('persist', $this->once(), [$this->ingredient]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->ingredientService->disconnectFromOZA($this->ingredient);

        // Assert
        $this->assertNull($this->ingredient->getOzaId());
    }

    #[Test]
    #[TestDox('Zwraca encję (Ingredient) użytkownika znalezioną w repozytorium')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'ingredientValues')]
    public function itFindsIngredient(int $id, ?IngredientEntity $ingredient): void
    {
        // Arrange
        $this->init(ingredientRepository: $this->getMock(
            IngredientRepository::class,
            new AllowedMethod('findById', $ingredient, $this->once(), [$id, $this->user]),
        ));

        // Act
        $result = $this->ingredientService->find($id);

        // Assert
        $this->assertSame($ingredient, $result);
    }

    #[Test]
    #[TestDox('Zwraca encję (Tag) użytkownika znalezioną w repozytorium - na podstawie ozaId')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'ingredientValues')]
    public function itFindsIngredientByOzaId(int $id, ?IngredientEntity $ingredient): void
    {
        // Arrange
        $this->init(ingredientRepository: $this->getMock(
            IngredientRepository::class,
            new AllowedMethod(
                'findOneBy',
                $ingredient,
                $this->once(),
                [['ozaId' => $id, 'user' => $this->user]],
            ),
        ));

        // Act
        $result = $this->ingredientService->findByOzaId($id);

        // Assert
        $this->assertSame($ingredient, $result);
    }

    #[Test]
    #[TestDox('Zwraca pierwszy Ingredient z Oza dla danego użytkownika')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'ingredientValues')]
    public function itGetsFirstIngredientWithOza(int $id, ?IngredientEntity $ingredient): void
    {
        // Arrange
        $this->init(ingredientRepository: $this->getMock(
            IngredientRepository::class,
            new AllowedMethod('findFirstIngredientWithOza', $ingredient, $this->once(), [$this->user]),
        ));

        // Act
        $result = $this->ingredientService->getFirstIngredientWithOza();

        // Assert
        $this->assertSame($ingredient, $result);
    }

    #[Test]
    #[TestDox('Odrzuca aktualizację encji (Ingredient), gdy formularz jest niepoprawny')]
    public function itRejectsInvalidForm(): void
    {
        // Arrange
        $ingredientClone = clone $this->ingredient;
        $this->init($this->getMock(EntityManagerInterface::class));
        $ingredientValidation = $this->getMock(
            EditIngredientValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
            new AllowedVoidMethod('setExpect', $this->once(), [$this->ingredient->getId()]),
        );

        // Act
        $result = $this->ingredientService->update($this->ingredient, $ingredientValidation);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals($ingredientClone, $this->ingredient);
    }

    #[Test]
    #[TestDox('Usuwa encję (Ingredient) z bazy danych')]
    public function itRemovesIngredient(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('remove', $this->once(), [$this->ingredient]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->ingredientService->remove($this->ingredient);
    }

    #[Test]
    #[TestDox('Aktualizuje encji (Ingredient) "available", gdy formularz jest poprawny')]
    #[DataProviderExternal(CommonDataProvider::class, 'boolValues')]
    public function itUpdatesAvailableValue(bool $value): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('persist', $this->once(), [$this->ingredient]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->ingredientService->updateAvailable($this->ingredient, $value);

        // Assert
        $this->assertSame($value, $this->ingredient->isAvailable());
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Ingredient), gdy formularz jest poprawny')]
    #[DataProviderExternal(UpdateEntityDataProvider::class, 'ingredientValues')]
    public function itUpdatesIngredientWhenFormIsValid(
        ?string $name,
        ?bool $available,
        ?int $ozaId,
        ?string $expectedName,
        ?int $expectedOzaId,
        ?bool $expectedAvailable,
    ): void {
        // Arrange
        $allowedMethods = [
            new AllowedMethod('getId', 1),
        ];
        if ($expectedName !== null) {
            $allowedMethods[] = new AllowedVoidMethod('setName', $this->once(), [$expectedName]);
        }
        if ($expectedOzaId !== null || $ozaId !== null || $expectedAvailable !== null) {
            $allowedMethods[] = new AllowedVoidMethod('setOzaId', $this->once(), [$expectedOzaId]);
        }
        if ($expectedAvailable !== null) {
            $allowedMethods[] = new AllowedVoidMethod('setAvailable', $this->once(), [$expectedAvailable]);
        }
        $ingredient = $this->getMock(IngredientEntity::class, ...$allowedMethods);
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('persist', $this->once(), [$ingredient]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );
        $ingredientValidation = $this->getMock(
            EditIngredientValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedVoidMethod('setExpect', $this->once(), [$ingredient->getId()]),
            new AllowedMethod('getDto', new EditIngredient($name, $available, $ozaId), $this->once()),
        );

        // Act
        $result = $this->ingredientService->update($ingredient, $ingredientValidation);

        // Assert
        $this->assertTrue($result);
    }

    private function init(
        ?EntityManagerInterface $entityManager = null,
        ?IngredientRepository $ingredientRepository = null,
    ): void {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->ingredientService = new IngredientService(
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $userService,
            $ingredientRepository ?? $this->createStub(IngredientRepository::class),
        );
    }
}
