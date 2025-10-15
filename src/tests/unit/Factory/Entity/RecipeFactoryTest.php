<?php

namespace App\Tests\Unit\Factory\Entity;

use App\Dto\Request\Recipe as RecipeRequest;
use App\Dto\Request\RecipePosition;
use App\Dto\Request\RecipePositionsGroup;
use App\Entity\Recipe;
use App\Entity\User;
use App\Factory\Entity\RecipeFactory;
use App\Service\RecipeFillService;
use App\Service\UserService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Utils\UuidGenerator;
use App\Validation\Recipe\RecipeValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(RecipeFactory::class)]
#[CoversClass(Recipe::class)]
#[CoversClass(RecipePosition::class)]
#[CoversClass(RecipePositionsGroup::class)]
#[CoversClass(RecipeRequest::class)]
class RecipeFactoryTest extends BaseTestCase
{
    private User $user;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
    }

    #[Test]
    #[TestDox('Tworzy encję (Recipe), gdy walidacja przeszła pomyślnie')]
    public function itCreatesRecipeOnValidInput(): void
    {
        // Arrange
        $recipeFillService = $this->createMock(RecipeFillService::class);
        $this->setupAllowedMethods(
            $recipeFillService,
            RecipeFillService::class,
            new AllowedMethod('fillRecipeBasicData', $recipeFillService, $this->once()),
            new AllowedMethod('assignTags', $recipeFillService, $this->once()),
            new AllowedMethod('assignPositions', $recipeFillService, $this->once()),
            new AllowedMethod('assignTimers', $recipeFillService, $this->once()),
        );
        $uuidGenerator = $this->getMock(
            UuidGenerator::class,
            new AllowedMethod('generate', 'uuid', $this->once()),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod(
                'persist',
                $this->once(),
                [$this->callback($this->recipeMatcher())],
            ),
            new AllowedVoidMethod('flush', $this->exactly(2)),
        );
        $factory = new RecipeFactory($entityManager, $this->userService, $recipeFillService, $uuidGenerator);
        $recipeValidation = $this->getMock(
            RecipeValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $this->createStub(RecipeRequest::class), $this->once()),
        );

        // Act
        $recipe = $factory->create($recipeValidation);

        // Assert
        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertSame('uuid', $recipe->getPublicId());
    }

    #[Test]
    #[TestDox('Odrzuca dane i przerywa tworzenie, gdy walidacja się nie powiodła')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $recipeFillService = $this->getMock(RecipeFillService::class);
        $uuidGenerator = $this->getMock(UuidGenerator::class);
        $factory = new RecipeFactory(
            $this->getMock(EntityManagerInterface::class),
            $this->userService,
            $recipeFillService,
            $uuidGenerator,
        );
        $recipeValidation = $this->getMock(
            RecipeValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $recipe = $factory->create($recipeValidation);

        // Assert
        $this->assertNull($recipe);
    }

    private function recipeMatcher(): callable
    {
        $user = $this->user;

        return static function ($recipe) use ($user): bool {
            return $recipe instanceof Recipe && $recipe->getUser() === $user;
        };
    }
}
