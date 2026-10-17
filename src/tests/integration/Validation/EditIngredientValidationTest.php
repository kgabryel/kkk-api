<?php

namespace App\Tests\Integration\Validation;

use App\Repository\IngredientRepository;
use App\Service\UserService;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\EditIngredientValidation;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(EditIngredientValidation::class)]
#[CoversClass(UniqueNameForUser::class)]
class EditIngredientValidationTest extends BaseIntegrationTestCase
{
    private EditIngredientValidation $editIngredientValidation;
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $validator = $container->get(ValidatorInterface::class);
        $ingredientRepository = $container->get(IngredientRepository::class);
        $this->request = $this->createStub(Request::class);
        $requestStack = new RequestStack([$this->request]);
        $userService = $this->createStub(UserService::class);
        $userService->method('getUser')->willReturn($this->defaultUser);
        $this->editIngredientValidation = new EditIngredientValidation(
            $validator,
            $requestStack,
            $userService,
            $ingredientRepository,
        );
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy nazwa jest używana przy edycji tego samego zasobu')]
    public function itPassesWhenNameIsUsedInEditMode(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient($this->defaultUser->getEmail(), ['name' => 'name']);
        $this->request->method('toArray')->willReturn(['name' => 'name', 'available' => true]);
        $this->editIngredientValidation->setExpect($ingredient->getId());

        // Act
        $result = $this->editIngredientValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }
}
