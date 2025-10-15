<?php

namespace App\Tests\Integration\Validation;

use App\Repository\IngredientRepository;
use App\Service\UserService;
use App\Tests\DataProvider\OzaSupplyDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\CreateIngredientValidation;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(CreateIngredientValidation::class)]
#[CoversClass(UniqueNameForUser::class)]
class CreateIngredientValidationTest extends BaseIntegrationTestCase
{
    private CreateIngredientValidation $createIngredientValidation;
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
        $this->createIngredientValidation = new CreateIngredientValidation(
            $validator,
            $requestStack,
            $userService,
            $ingredientRepository,
        );
    }

    #[Test]
    #[TestDox('Domyślnie ustawia ozaId na null')]
    public function itDefaultsOzaIdToNull(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn($this->getRequestData());
        $this->createIngredientValidation->validate();

        // Act
        $dto = $this->createIngredientValidation->getDto();

        // Assert
        $this->assertNull($dto->getOzaId());
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy nazwa jest już wykorzystywana')]
    public function itFailsWhenNameIsAlreadyUsed(): void
    {
        // Arrange
        EntityFactory::createIngredient($this->defaultUser->getEmail(), ['name' => 'name']);
        $this->request->method('toArray')->willReturn($this->getRequestData());

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $result->getRawErrors(),
            'This name is already used.',
        );
    }

    #[Test]
    #[TestDox('Przypisuje poprawnie ozaId do DTO')]
    #[DataProviderExternal(OzaSupplyDataProvider::class, 'ozaIdValues')]
    public function itMapOzaIdCorrectly(?int $requestId, ?int $expectedId): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn($this->getRequestData(['ozaId' => $requestId]));
        $this->createIngredientValidation->validate();

        // Act
        $dto = $this->createIngredientValidation->getDto();

        // Assert
        $this->assertSame($expectedId, $dto->getOzaId());
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy nazwa nie jest wykorzystywana')]
    public function itPassesWhenNameIsUnused(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn($this->getRequestData());

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy nazwa jest używana przez innego użytkownika')]
    public function itPassesWhenNameIsUsedByAnotherUser(): void
    {
        // Arrange
        EntityFactory::createIngredient(EntityFactory::USER_EMAIL_2, ['name' => 'name']);
        $this->request->method('toArray')->willReturn($this->getRequestData());

        // Act
        $result = $this->createIngredientValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }

    private function getRequestData(array $overrides = []): array
    {
        $defaults = ['name' => 'name', 'available' => true];

        return array_merge($defaults, $overrides);
    }
}
