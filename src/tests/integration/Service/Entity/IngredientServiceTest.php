<?php

namespace App\Tests\Integration\Service\Entity;

use App\Service\Entity\IngredientService;
use App\Service\UserService;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\EditIngredientValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[Medium]
#[CoversClass(IngredientService::class)]
class IngredientServiceTest extends BaseIntegrationTestCase
{
    private IngredientService $ingredientService;
    private EditIngredientValidation $ingredientValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $userService = $this->createStub(UserService::class);
        $userService->method('getUser')->willReturn($this->defaultUser);
        $container->set(UserService::class, $userService);
        $this->ingredientService = $container->get(IngredientService::class);
        $this->ingredientValidation = $container->get(EditIngredientValidation::class);
    }

    #[Test]
    #[TestDox('Pozwala dokanać aktualizacji z podaniem aktualnej nazwy')]
    public function itAllowsUpdatingWithSameName(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient($this->defaultUser->getEmail(), ['name' => 'name']);
        $this->setRequestData(['name' => 'name']);

        // Act
        $result = $this->ingredientService->update($ingredient, $this->ingredientValidation);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('Odrzuca aktualizację przy duplikacji nazwy')]
    public function itRejectNameDuplicate(): void
    {
        // Arrange
        EntityFactory::createIngredient($this->defaultUser->getEmail(), ['name' => 'name']);
        $ingredient2 = EntityFactory::createIngredient($this->defaultUser->getEmail(), ['name' => 'name2']);
        $this->setRequestData(['name' => 'name']);

        // Act
        $result = $this->ingredientService->update($ingredient2, $this->ingredientValidation);

        // Assert
        $this->assertFalse($result);
    }

    private function setRequestData(array $data): void
    {
        $container = self::getContainer();
        $requestStack = $container->get(RequestStack::class);
        $request = $this->createStub(Request::class);
        $request->method('toArray')->willReturn($data);
        $requestStack->push($request);
    }
}
