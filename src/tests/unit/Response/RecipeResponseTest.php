<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\Recipe;
use App\Response\RecipeResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(RecipeResponse::class)]
class RecipeResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - Recipe')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $recipe = EntityFactory::getSimpleRecipe();
        $this->setupFactoryForSingleEntity([Recipe::class, $recipe]);

        // Act
        new RecipeResponse($this->dtoFactory, $recipe, Response::HTTP_OK);
    }
}
