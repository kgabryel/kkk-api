<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\FullRecipe;
use App\Response\FullRecipeResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(FullRecipeResponse::class)]
class FullRecipeResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - Recipe -> FullRecipe')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $recipe = EntityFactory::getSimpleRecipe();
        $this->setupFactoryForSingleEntity([FullRecipe::class, $recipe]);

        // Act
        new FullRecipeResponse($this->dtoFactory, $recipe);
    }
}
