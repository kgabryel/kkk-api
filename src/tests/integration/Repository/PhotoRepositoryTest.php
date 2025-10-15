<?php

namespace App\Tests\integration\Repository;

use App\Config\PhotoType;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Repository\PhotoRepository;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Medium]
#[CoversClass(PhotoRepository::class)]
class PhotoRepositoryTest extends BaseIntegrationTestCase
{
    private PhotoRepository $photoRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->photoRepository = self::getContainer()->get(PhotoRepository::class);
    }

    #[Test]
    #[TestDox('Zwraca numer większy o jeden od najwyższego photoOrder')]
    public function itReturnsMaxPhotoOrderPlusOne(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe($this->defaultUser->getEmail());
        $this->addPhoto($recipe, 2);
        $this->addPhoto($recipe, 5);
        $this->addPhoto($recipe, 3);

        // Act
        $order = $this->photoRepository->getNextPhotoOrderForRecipe($recipe);

        // Assert
        $this->assertSame(6, $order);
    }

    #[Test]
    #[TestDox('Zwraca 1 gdy przepis nie ma żadnych zdjęć')]
    public function itReturnsOneWhenRecipeHasNoPhotos(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe($this->defaultUser->getEmail());

        // Act
        $order = $this->photoRepository->getNextPhotoOrderForRecipe($recipe);

        // Assert
        $this->assertSame(1, $order);
    }

    private function addPhoto(Recipe $recipe, int $photoOrder): void
    {
        $photo = new Photo();
        $photo->setRecipe($recipe)
            ->setFileName('name')
            ->setHeight(100)
            ->setWidth(100)
            ->setType(PhotoType::ORIGINAL->value)
            ->setUser($recipe->getUser());
        $photo->setPhotoOrder($photoOrder);
        $this->save($photo);
    }
}
