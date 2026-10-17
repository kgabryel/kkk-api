<?php

namespace App\Tests\Unit\Dto\Entity;

use App\Dto\Entity\FullRecipePosition;
use App\Tests\DataProvider\RecipeDataProvider;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(FullRecipePosition::class)]
class FullRecipePositionTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO i wybiera odpowiednie wartości ingredient/recipe')]
    #[DataProviderExternal(RecipeDataProvider::class, 'positionPartsValues')]
    public function itBuildsDto(?string $ingredient, ?string $recipe, string $expected): void
    {
        // Act
        $dto = new FullRecipePosition(null, 'kg', false, $ingredient, $recipe);

        // Assert
        $this->assertSame($expected, $dto->jsonSerialize()['ingredient']);
    }
}
