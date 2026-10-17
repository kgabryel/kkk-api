<?php

namespace App\Tests\Unit\Service\Entity;

use App\Config\PhotoType;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\PhotoRepository;
use App\Service\Entity\PhotoService;
use App\Service\UserService;
use App\Tests\DataProvider\FindEntityDataProvider;
use App\Tests\DataProvider\UpdateEntityDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Utils\PhotoUtils;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

#[Small]
#[CoversClass(PhotoService::class)]
class PhotoServiceTest extends BaseTestCase
{
    private Photo $photo;
    private PhotoService $photoService;
    private Recipe $recipe;
    private User $user;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->photo = EntityFactory::getSimplePhoto();
        $this->recipe = EntityFactory::getSimpleRecipe();
    }

    #[Test]
    #[TestDox('Odmowa dostępu do zdjęcia, gdy użytkownik nie jest właścicielem niepublicznego przepisu')]
    #[DataProviderExternal(UpdateEntityDataProvider::class, 'photoAccessValues')]
    public function itAllowsOnlyOwnerWhenPhotoPrivate(
        int $connectedUserId,
        ?int $checkingUserId,
        bool $result,
    ): void {
        // Arrange
        $this->user = EntityFactory::getSimpleUser($connectedUserId);
        $this->photo->setUser($this->user);
        $this->recipe->setPublic(false);
        $this->photo->setRecipe($this->recipe);
        $this->init();
        $checkingUser = $checkingUserId !== null ? EntityFactory::getSimpleUser($checkingUserId) : null;

        // Act
        $access = $this->photoService->checkAccess($this->photo, $checkingUser);

        // Assert
        $this->assertSame($result, $access);
    }

    #[Test]
    #[TestDox('Zwraca encję (Photo) użytkownika znalezioną w repozytorium')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'photoValues')]
    public function itFindsPhoto(int $id, ?Photo $photo): void
    {
        // Arrange
        $this->init(
            photoRepository: $this->getMock(
                PhotoRepository::class,
                new AllowedMethod('findById', $photo, $this->once(), [$id, $this->user]),
            ),
        );

        // Act
        $result = $this->photoService->find($id);

        // Assert
        $this->assertSame($photo, $result);
    }

    #[Test]
    #[TestDox('Zezwala na dostęp do zdjęcia, gdy przepis jest publiczny')]
    public function itGrantsAccessWhenPhotoIsPublic(): void
    {
        // Arrange
        $this->recipe->setPublic(true);
        $this->photo->setRecipe($this->recipe);
        $this->init();

        // Act
        $result = $this->photoService->checkAccess($this->photo, null);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('Usuwa encję (Photo) z bazy danych')]
    public function itRemovesPhoto(): void
    {
        // Arrange
        $fileName = 'example.jpg';
        $this->photo->setFileName($fileName);
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('remove', $this->once(), [$this->photo]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $actualPaths = [];
        $expectedPaths = array_map(
            static fn (PhotoType $type): string => PhotoUtils::getPath('', $type, $fileName),
            PhotoType::cases(),
        );
        $filesystem = $this->getMock(
            Filesystem::class,
            new AllowedCallbackMethod(
                'remove',
                function (string $path) use (&$actualPaths): void {
                    $actualPaths[] = $path;
                },
                $this->exactly(count(PhotoType::cases())),
            ),
        );
        $this->init($entityManager, filesystem: $filesystem, kernel: $this->createStub(KernelInterface::class));

        // Act
        $this->photoService->remove($this->photo);

        // Assert
        $this->assertEqualsCanonicalizing($expectedPaths, $actualPaths);
    }

    private function init(
        ?EntityManagerInterface $entityManager = null,
        ?PhotoRepository $photoRepository = null,
        ?Filesystem $filesystem = null,
        ?KernelInterface $kernel = null,
    ): void {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->photoService = new PhotoService(
            $entityManager ?? $this->getMock(EntityManagerInterface::class),
            $userService,
            $photoRepository ?? $this->getMock(PhotoRepository::class),
            $filesystem ?? $this->getMock(Filesystem::class),
            $kernel ?? $this->getMock(KernelInterface::class),
        );
    }
}
