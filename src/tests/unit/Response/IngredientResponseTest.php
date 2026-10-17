<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\Ingredient;
use App\Response\IngredientResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(IngredientResponse::class)]
class IngredientResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - Ingredient')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $ingredient = EntityFactory::getSimpleIngredient();
        $this->setupFactoryForSingleEntity([Ingredient::class, $ingredient]);

        // Act
        new IngredientResponse($this->dtoFactory, $ingredient, Response::HTTP_OK);
    }
}
