<?php

namespace App\Tests\Integration\Validation;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use App\Repository\SeasonRepository;
use App\Service\UserService;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\SeasonValidation;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(SeasonValidation::class)]
#[CoversClass(UniqueNameForUser::class)]
class SeasonValidationTest extends BaseIntegrationTestCase
{
    private Ingredient $ingredient;
    private Request $request;
    private SeasonValidation $seasonValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ingredient = EntityFactory::createIngredient($this->defaultUser->getEmail());
        $container = self::getContainer();
        $validator = $container->get(ValidatorInterface::class);
        $ingredientRepository = $container->get(IngredientRepository::class);
        $seasonRepository = $container->get(SeasonRepository::class);
        $this->request = $this->createStub(Request::class);
        $requestStack = new RequestStack([$this->request]);
        $userService = $this->createStub(UserService::class);
        $userService->method('getUser')->willReturn($this->defaultUser);
        $this->seasonValidation = new SeasonValidation(
            $validator,
            $requestStack,
            $userService,
            $ingredientRepository,
            $seasonRepository,
        );
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    public function itAcceptsValidData(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            ['start' => 1, 'stop' => 2, 'ingredient' => $this->ingredient->getId()],
        );

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy składnik jest już wykorzystywany')]
    public function itFailsWhenIngredientIsAlreadyUsed(): void
    {
        // Arrange
        EntityFactory::createSeason($this->defaultUser->getEmail(), ['ingredient' => $this->ingredient]);
        $this->request->method('toArray')->willReturn(
            $this->getRequestData(['ingredient' => $this->ingredient->getId()]),
        );

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $result->getRawErrors(),
            'Ingredient is already in use.',
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy składnik nie istnieje')]
    public function itFailsWhenWhenIngredientNotExists(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn($this->getRequestData(['ingredient' => 2]));

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $result->getRawErrors(),
            'No matching item found for this user.',
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy składnik nie należy do użytkownika')]
    public function itFailsWhenWhenIngredientUnavailable(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient(EntityFactory::USER_EMAIL_2);
        $this->request->method('toArray')->willReturn(
            $this->getRequestData(['ingredient' => $ingredient->getId()]),
        );

        // Act
        $result = $this->seasonValidation->validate();

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $result->getRawErrors(),
            'No matching item found for this user.',
        );
    }

    private function getRequestData(array $overrides = []): array
    {
        $defaults =  [
            'ingredient' => 1,
            'start' => 1,
            'stop' => 2,
        ];

        return array_merge($defaults, $overrides);
    }
}
