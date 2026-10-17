<?php

namespace App\Tests\Unit\Service\Entity;

use App\Dto\Request\EditSeason;
use App\Entity\Season;
use App\Entity\User;
use App\Repository\SeasonRepository;
use App\Service\Entity\SeasonService;
use App\Service\UserService;
use App\Tests\DataProvider\FindEntityDataProvider;
use App\Tests\DataProvider\UpdateEntityDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\EditSeasonValidation;
use App\Validation\SeasonValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(SeasonService::class)]
#[CoversClass(EditSeason::class)]
class SeasonServiceTest extends BaseTestCase
{
    private Season $season;
    private SeasonService $seasonService;
    private User $user;

    protected function setUp(): void
    {
        $this->season = EntityFactory::getSimpleSeason();
        $this->user = EntityFactory::getSimpleUser();
    }

    #[Test]
    #[TestDox('Zwraca encję (Season) użytkownika znalezioną w repozytorium')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'seasonValues')]
    public function itFindsSeason(int $id, ?Season $season): void
    {
        // Arrange
        $this->init(seasonRepository: $this->getMock(
            SeasonRepository::class,
            new AllowedMethod('findById', $season, $this->once(), [$id, $this->user]),
        ));

        // Act
        $result = $this->seasonService->find($id);

        // Assert
        $this->assertSame($season, $result);
    }

    #[Test]
    #[TestDox('Odrzuca aktualizację encji (Season), gdy formularz jest niepoprawny')]
    public function itRejectsInvalidForm(): void
    {
        // Arrange
        $seasonClone = clone $this->season;
        $this->init($this->getMock(EntityManagerInterface::class));
        $tagValidation = $this->getMock(
            SeasonValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $result = $this->seasonService->update($this->season, $tagValidation);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals($seasonClone, $this->season);
    }

    #[Test]
    #[TestDox('Usuwa encję (Season) z bazy danych')]
    public function itRemovesSeason(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('remove', $this->once(), [$this->season]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->seasonService->remove($this->season);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Season), gdy formularz jest poprawny')]
    #[DataProviderExternal(UpdateEntityDataProvider::class, 'seasonValues')]
    public function itUpdatesSeasonWhenFormIsValid(int $start, int $stop): void
    {
        // Arrange
        $seasonClone = clone $this->season;
        $seasonClone->setStart($start);
        $seasonClone->setStop($stop);
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('persist', $this->once(), [$this->season]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );
        $seasonModel = new EditSeason($start, $stop);
        $seasonValidation = $this->getMock(
            EditSeasonValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $seasonModel, $this->once()),
        );

        // Act
        $result = $this->seasonService->update($this->season, $seasonValidation);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals($seasonClone, $this->season);
    }

    private function init(
        ?EntityManagerInterface $entityManager = null,
        ?SeasonRepository $seasonRepository = null,
    ): void {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->seasonService = new SeasonService(
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $userService,
            $seasonRepository ?? $this->createStub(SeasonRepository::class),
        );
    }
}
