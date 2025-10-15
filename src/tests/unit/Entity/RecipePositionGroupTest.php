<?php

namespace App\Tests\Unit\Entity;

use App\Entity\RecipePositionGroup;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(RecipePositionGroup::class)]
class RecipePositionGroupTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca pozycje w poprawnej kolejności - rosnącej')]
    public function itReturnsOrderedPositions(): void
    {
        // Arrange
        $position1 = EntityFactory::getSimpleRecipePosition(2);
        $position2 = EntityFactory::getSimpleRecipePosition();
        $position3 = EntityFactory::getSimpleRecipePosition(3);
        $recipe = new RecipePositionGroup();
        $recipe->addRecipePosition($position1);
        $recipe->addRecipePosition($position2);
        $recipe->addRecipePosition($position3);

        // Act
        $positions = $recipe->getRecipePositions()->toArray();

        // Assert
        $this->assertSame([$position2, $position1, $position3], $positions);
    }
}
