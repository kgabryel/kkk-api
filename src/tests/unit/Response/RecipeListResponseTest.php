<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\List\RecipeList;
use App\Response\RecipeListResponse;
use App\Tests\DataProvider\SimpleEntityDataProvider;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(RecipeListResponse::class)]
class RecipeListResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO na podstawie encji - Recipe -> RecipeList')]
    #[DataProviderExternal(SimpleEntityDataProvider::class, 'recipesValues')]
    public function itCallsDtoFactoryWithCorrectParams(array $recipes): void
    {
        // Arrange
        $this->setupFactoryForList(new RecipeList(), [RecipeList::class, ...$recipes]);

        // Act
        new RecipeListResponse($this->dtoFactory, ...$recipes);
    }
}
