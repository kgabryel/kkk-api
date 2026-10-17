<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\List\IngredientList;
use App\Response\IngredientListResponse;
use App\Tests\DataProvider\SimpleEntityDataProvider;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(IngredientListResponse::class)]
class IngredientListResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO na podstawie encji - Ingredient -> IngredientList')]
    #[DataProviderExternal(SimpleEntityDataProvider::class, 'ingredientsValues')]
    public function itCallsDtoFactoryWithCorrectParams(array $ingredients): void
    {
        // Arrange
        $this->setupFactoryForList(new IngredientList(), [IngredientList::class, ...$ingredients]);

        // Act
        new IngredientListResponse($this->dtoFactory, ...$ingredients);
    }
}
