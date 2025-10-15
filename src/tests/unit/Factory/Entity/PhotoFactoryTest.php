<?php

namespace App\Tests\Unit\Factory\Entity;

use App\Dto\Request\Photo as PhotoRequest;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Entity\User;
use App\Factory\Entity\PhotoFactory;
use App\Repository\PhotoRepository;
use App\Service\Photo\PhotoDimensionValidator;
use App\Service\Photo\PhotoScaler;
use App\Service\Photo\PhotoStorage;
use App\Service\UserService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\FixtureHelper;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Utils\PhotoUtils;
use App\Utils\UuidGenerator;
use App\Validation\PhotoValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(PhotoFactory::class)]
#[CoversClass(Photo::class)]
#[CoversClass(PhotoRequest::class)]
#[CoversClass(PhotoUtils::class)]
class PhotoFactoryTest extends BaseTestCase
{
    private PhotoFactory $photoFactory;
    private Recipe $recipe;
    private User $user;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->recipe = EntityFactory::getSimpleRecipe();
        $this->userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
    }

    #[Test]
    #[TestDox('Tworzy encję (Photo), gdy walidacja przeszła pomyślnie')]
    public function itCreatesPhotoOnValidInput(): void
    {
        // Arrange
        $base64Image = FixtureHelper::getFixture('png_600x800.txt');
        $validation = $this->getMock(
            PhotoValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new PhotoRequest(base64_decode($base64Image)), $this->once()),
        );
        $recipe = $this->getMock(
            Recipe::class,
            new AllowedVoidMethod('addPhoto', $this->once()),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once()),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $photoStorage = $this->getMock(
            PhotoStorage::class,
            new AllowedVoidMethod('saveFile', $this->exactly(3)),
        );
        $photoRepository = $this->getMock(
            PhotoRepository::class,
            new AllowedMethod('getNextPhotoOrderForRecipe', 1, $this->once()),
        );
        $photoScaler = $this->createStub(PhotoScaler::class);
        $photoDimensionValidator = $this->getMock(
            PhotoDimensionValidator::class,
            new AllowedMethod('isValid', true, $this->once()),
        );
        $uuidGenerator = $this->getMock(
            UuidGenerator::class,
            new AllowedMethod('generate', 'uuid', $this->once()),
        );
        $this->initPhotoFactory(
            $entityManager,
            $photoStorage,
            $photoRepository,
            $photoScaler,
            $photoDimensionValidator,
            $uuidGenerator,
        );

        // Act
        $result = $this->photoFactory->create($validation, $recipe);

        // Assert
        $this->assertInstanceOf(Photo::class, $result);
        $this->assertSame('uuid', $result->getFileName());
        $this->assertSame($this->user, $result->getUser());
        $this->assertSame(800, $result->getWidth());
        $this->assertSame(600, $result->getHeight());
        $this->assertSame('image/png', $result->getType());
        $this->assertSame(1, $result->getPhotoOrder());
    }

    #[Test]
    #[TestDox('Zwraca false, gdy wystąpi wyjątek podczas tworzenia')]
    public function itFailsWhenExceptionIsThrown(): void
    {
        // Arrange
        $base64Image = '';
        $dto = new PhotoRequest(base64_decode($base64Image));
        $validation = $this->getMock(
            PhotoValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $dto, $this->once()),
        );
        $this->initPhotoFactory();

        // Act
        $result = $this->photoFactory->create($validation, $this->recipe);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Przerywa tworzenie, gdy wymiary zdjęcia są niepoprawne')]
    public function itFailsWhenPhotoDimensionsInvalid(): void
    {
        // Arrange
        $base64Image = FixtureHelper::getFixture('png_600x800.txt');
        $dto = new PhotoRequest(base64_decode($base64Image));
        $validation = $this->getMock(
            PhotoValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $dto, $this->once()),
        );
        $photoDimensionValidator = $this->getMock(
            PhotoDimensionValidator::class,
            new AllowedMethod('isValid', false, $this->once()),
        );
        $this->initPhotoFactory(photoDimensionValidator: $photoDimensionValidator);

        // Act
        $result = $this->photoFactory->create($validation, $this->recipe);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Odrzuca dane i przerywa tworzenie, gdy walidacja się nie powiodła')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $validation = $this->getMock(
            PhotoValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );
        $this->initPhotoFactory();

        // Act
        $result = $this->photoFactory->create($validation, $this->recipe);

        // Assert
        $this->assertFalse($result);
    }

    private function initPhotoFactory(
        ?EntityManagerInterface $entityManager = null,
        ?PhotoStorage $photoStorage = null,
        ?PhotoRepository $photoRepository = null,
        ?PhotoScaler $photoScaler = null,
        ?PhotoDimensionValidator $photoDimensionValidator = null,
        ?UuidGenerator $uuidGenerator = null,
    ): void {
        $this->photoFactory = new PhotoFactory(
            $entityManager ?? $this->getMock(EntityManagerInterface::class),
            $this->userService,
            $photoStorage ?? $this->getMock(PhotoStorage::class),
            $photoRepository ?? $this->getMock(PhotoRepository::class),
            $photoScaler ?? $this->getMock(PhotoScaler::class),
            $photoDimensionValidator ?? $this->getMock(PhotoDimensionValidator::class),
            $uuidGenerator ?? $this->createStub(UuidGenerator::class),
        );
    }
}
