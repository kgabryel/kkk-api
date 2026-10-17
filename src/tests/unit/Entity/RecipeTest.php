<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Photo;
use App\Entity\Recipe;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(Recipe::class)]
class RecipeTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca zdjęcia w poprawnej kolejności - rosnącej')]
    public function itReturnsOrderedPhotos(): void
    {
        // Arrange
        $photo1 = new Photo();
        $photo1->setPhotoOrder(2);
        $photo2 = new Photo();
        $photo2->setPhotoOrder(1);
        $photo3 = new Photo();
        $photo3->setPhotoOrder(3);
        $recipe = new Recipe();
        $recipe->addPhoto($photo1);
        $recipe->addPhoto($photo2);
        $recipe->addPhoto($photo3);

        // Assert
        $this->assertSame([$photo2, $photo1, $photo3], $recipe->getPhotos()->toArray());
    }

    #[Test]
    #[TestDox('Zwraca grupy w poprawnej kolejności - rosnącej')]
    public function itReturnsOrderedRecipePositionGroups(): void
    {
        // Arrange
        $group1 = EntityFactory::getSimpleRecipePositionGroup(2);
        $group2 = EntityFactory::getSimpleRecipePositionGroup();
        $group3 = EntityFactory::getSimpleRecipePositionGroup(3);
        $recipe = new Recipe();
        $recipe->addRecipePositionGroup($group1);
        $recipe->addRecipePositionGroup($group2);
        $recipe->addRecipePositionGroup($group3);

        // Assert
        $this->assertSame([$group2, $group1, $group3], $recipe->getRecipePositionGroups()->toArray());
    }

    #[Test]
    #[TestDox('Zwraca tagi w poprawnej kolejności - rosnącej')]
    public function itReturnsOrderedTags(): void
    {
        // Arrange
        $tag1 = EntityFactory::getSimpleTag(2);
        $tag2 = EntityFactory::getSimpleTag();
        $tag3 = EntityFactory::getSimpleTag(3);
        $recipe = new Recipe();
        $recipe->addTag($tag1);
        $recipe->addTag($tag2);
        $recipe->addTag($tag3);

        // Assert
        $this->assertSame([$tag2, $tag1, $tag3], $recipe->getTags()->toArray());
    }

    #[Test]
    #[TestDox('Zwraca timery w poprawnej kolejności - rosnącej')]
    public function itReturnsOrderedTimers(): void
    {
        // Arrange
        $timer1 = EntityFactory::getSimpleTimer(2);
        $timer2 = EntityFactory::getSimpleTimer();
        $timer3 = EntityFactory::getSimpleTimer(3);
        $recipe = new Recipe();
        $recipe->addTimer($timer1);
        $recipe->addTimer($timer2);
        $recipe->addTimer($timer3);

        // Act
        $timers = $recipe->getTimers()->toArray();

        // Assert
        $this->assertSame([$timer2, $timer1, $timer3], $timers);
    }
}
