<?php

namespace App\Tests\Unit\Factory\Entity;

use App\Dto\Request\Season as SeasonRequest;
use App\Entity\Ingredient;
use App\Entity\Season;
use App\Entity\User;
use App\Factory\Entity\SeasonFactory;
use App\Service\UserService;
use App\Tests\DataProvider\EntityFactoryDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\SeasonValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(SeasonFactory::class)]
#[CoversClass(Season::class)]
#[CoversClass(SeasonRequest::class)]
class SeasonFactoryTest extends BaseTestCase
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
    #[TestDox('Tworzy encję (Season), gdy walidacja przeszła pomyślnie')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'seasonDataValues')]
    public function itCreatesSeasonOnValidInput(Ingredient $ingredient, int $start, int $stop): void
    {
        // Arrange
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod(
                'persist',
                $this->once(),
                [$this->callback($this->seasonMatcher($ingredient, $start, $stop))],
            ),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $factory = new SeasonFactory($entityManager, $this->userService);
        $seasonModel = new SeasonRequest($ingredient, $start, $stop);
        $seasonValidation = $this->getMock(
            SeasonValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $seasonModel, $this->once()),
        );

        // Act
        $season = $factory->create($seasonValidation);

        // Assert
        $this->assertInstanceOf(Season::class, $season);
    }

    #[Test]
    #[TestDox('Odrzuca dane i przerywa tworzenie, gdy walidacja się nie powiodła')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $factory = new SeasonFactory($this->getMock(EntityManagerInterface::class), $this->userService);
        $seasonValidation = $this->getMock(
            SeasonValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $season = $factory->create($seasonValidation);

        // Assert
        $this->assertNull($season);
    }

    private function seasonMatcher(Ingredient $ingredient, int $start, int $stop): callable
    {
        $user = $this->user;

        return static function ($season) use ($user, $ingredient, $start, $stop): bool {
            return $season instanceof Season
                && $season->getIngredient() === $ingredient
                && $season->getStart() === $start
                && $season->getStop() === $stop
                && $season->getUser() === $user;
        };
    }
}
