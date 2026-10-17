<?php

namespace App\Tests\integration\Repository;

use App\Repository\IngredientRepository;
use App\Tests\DataProvider\OzaSupplyDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Medium]
#[CoversClass(IngredientRepository::class)]
class IngredientRepositoryTest extends BaseIntegrationTestCase
{
    private IngredientRepository $ingredientRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ingredientRepository = self::getContainer()->get(IngredientRepository::class);
    }

    #[Test]
    #[TestDox('Resetuje ozaId tylko dla składników podanego użytkownika')]
    public function itResetsOzaIdsOnlyForGivenUser(): void
    {
        // Arrange
        $ingredient1 = EntityFactory::createIngredient($this->defaultUser->getEmail(), ['ozaId' => 1]);
        $ingredient2 = EntityFactory::createIngredient($this->defaultUser->getEmail(), ['ozaId' => 2]);
        $otherUserIngredient = EntityFactory::createIngredient(EntityFactory::USER_EMAIL_2, ['ozaId' => 3]);

        // Act
        $affectedRows = $this->ingredientRepository->resetOzaIdsForUser($this->defaultUser);

        // Refresh entities from DB
        $this->entityManager->refresh($ingredient1);
        $this->entityManager->refresh($ingredient2);
        $this->entityManager->refresh($otherUserIngredient);

        // Assert
        $this->assertSame(2, $affectedRows);
        $this->assertNull($ingredient1->getOzaId());
        $this->assertNull($ingredient2->getOzaId());
        $this->assertSame(3, $otherUserIngredient->getOzaId());
    }

    #[Test]
    #[TestDox('Zwraca pierwszy składnik z niepustym ozaId dla użytkownika')]
    #[DataProviderExternal(OzaSupplyDataProvider::class, 'ingredientsWithOzaIdValues')]
    public function testFindsFirstIngredientWithOza(array $ozaIds, ?int $expectedOzaId): void
    {
        // Arrange
        foreach ($ozaIds as $ozaId) {
            EntityFactory::createIngredient($this->defaultUser->getEmail(), ['ozaId' => $ozaId]);
        }

        // Act
        $ingredient = $this->ingredientRepository->findFirstIngredientWithOza($this->defaultUser);

        // Assert
        if ($expectedOzaId === null) {
            $this->assertNull($ingredient);
        } else {
            $this->assertNotNull($ingredient);
            $this->assertSame($expectedOzaId, $ingredient->getOzaId());
        }
    }
}
