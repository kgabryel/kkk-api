<?php

namespace App\Tests\Unit\Controller;

use App\Config\PhotoType;
use App\Controller\PhotosController;
use App\Dto\Entity\Recipe;
use App\Entity\Photo;
use App\Entity\Recipe as RecipeEntity;
use App\Entity\User;
use App\Factory\DtoFactoryDispatcher;
use App\Factory\Entity\PhotoFactory;
use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use App\Response\RecipeResponse;
use App\Service\Entity\PhotoService;
use App\Service\Entity\RecipeService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\PhotoValidation;
use App\Validation\ReorderPhotosValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

#[Small]
#[CoversClass(PhotosController::class)]
class PhotosControllerTest extends BaseTestCase
{
    private PhotosController $controller;
    private Photo $photo;
    private PhotoValidation $photoValidation;
    private RecipeEntity $recipe;
    private ReorderPhotosValidation $reorderPhotosValidation;
    private User $user;

    protected function setUp(): void
    {
        $this->recipe = EntityFactory::getSimpleRecipe();
        $this->photo = EntityFactory::getSimplePhoto();
        $this->user = EntityFactory::getSimpleUser();
        $this->reorderPhotosValidation = $this->createStub(ReorderPhotosValidation::class);
        $this->photoValidation = $this->createStub(PhotoValidation::class);
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('get', $this->createStub(Recipe::class)),
        );
        $userRepository = $this->getMock(UserRepository::class);
        $this->controller = $this->getMockBuilder(PhotosController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn($this->user);
    }

    #[Test]
    #[TestDox('Wywołuje reorderPhotos, gdy przepis istnieje i należy do bieżącego użytkownika')]
    public function itCallsReorderPhotosWhenRecipeAvailable(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
            new AllowedMethod(
                'reorderPhotos',
                true,
                $this->once(),
                [$this->recipe, $this->reorderPhotosValidation],
            ),
        );

        // Act
        $response = $this->controller->reorderPhotos(1, $recipeService, $this->reorderPhotosValidation);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Usuwa encję (Photo) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserPhotoWhenAvailable(): void
    {
        // Arrange
        $this->photo->setRecipe($this->recipe);
        $photoService = $this->getMock(
            PhotoService::class,
            new AllowedMethod('find', $this->photo, $this->once(), [1]),
            new AllowedVoidMethod('remove', $this->once(), [$this->photo]),
        );

        // Act
        $response = $this->controller->destroy(1, $photoService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Photo) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsDeletePhotoWhenUnavailable(): void
    {
        // Arrange
        $photoService = $this->getMock(
            PhotoService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->destroy(1, $photoService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie zmiany kolejności zdjęć i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnReorderPhotos(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
            new AllowedMethod(
                'reorderPhotos',
                false,
                $this->once(),
                [$this->recipe, $this->reorderPhotosValidation],
            ),
        );

        // Act
        $response = $this->controller->reorderPhotos(1, $recipeService, $this->reorderPhotosValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Photo) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Arrange
        $photoFactory = $this->getMock(
            PhotoFactory::class,
            new AllowedMethod('create', false, $this->once(), [$this->photoValidation]),
        );
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->store(1, $recipeService, $photoFactory, $this->photoValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie zmiany kolejności zdjęć i zwraca 404, gdy przepis jest niedostępny')]
    public function itRejectsReorderPhotosWhenRecipeUnavailable(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->reorderPhotos(1, $recipeService, $this->reorderPhotosValidation);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utworzenia encji (Photo) i zwraca 404, gdy przepis jest niedostępny')]
    public function itRejectsStoreWhenRecipeUnavailable(): void
    {
        // Arrange
        $photoFactory = $this->getMock(PhotoFactory::class);
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->store(1, $recipeService, $photoFactory, $this->photoValidation);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy zdjęcie nie istnieje lub nie jest publiczne')]
    public function itReturnsForbiddenIfPhotoNotFound(): void
    {
        // Arrange
        $kernel = $this->getMock(KernelInterface::class);
        $photoRepository = $this->getMock(
            PhotoRepository::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );
        $photoService = $this->getMock(PhotoService::class);

        // Act
        $response = $this->controller->show(
            PhotoType::SMALL->value,
            1,
            $photoRepository,
            $kernel,
            $photoService,
        );

        // Assert
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy użytkownik nie ma dostępu do zdjęcia')]
    public function itReturnsForbiddenWhenNoPhotoAccess(): void
    {
        // Arrange
        $kernel = $this->getMock(KernelInterface::class);
        $photoRepository = $this->getMock(
            PhotoRepository::class,
            new AllowedMethod('find', $this->photo, $this->once(), [1]),
        );
        $photoService = $this->getMock(
            PhotoService::class,
            new AllowedMethod('checkAccess', false, $this->once(), [$this->photo, $this->user]),
        );

        // Act
        $response = $this->controller->show(
            PhotoType::SMALL->value,
            1,
            $photoRepository,
            $kernel,
            $photoService,
        );

        // Assert
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca zdjęcie ze statusem 200, gdy użytkownik ma dostęp')]
    public function itReturnsPhotoIfUserHasAccess(): void
    {
        // Arrange
        $this->photo->setType('image/png');
        $this->photo->setFileName('test.png');
        $photoRepository = $this->getMock(
            PhotoRepository::class,
            new AllowedMethod('find', $this->photo, $this->once(), [1]),
        );
        $photoService = $this->getMock(
            PhotoService::class,
            new AllowedMethod('checkAccess', true, $this->once(), [$this->photo, $this->user]),
        );
        $kernel = $this->getMock(
            KernelInterface::class,
            new AllowedMethod('getProjectDir', '/tmp'),
        );

        // Stub pliku
        $fakeFilePath = '/tmp/var/files/small/test.png';
        if (!is_dir(dirname($fakeFilePath))) {
            mkdir(dirname($fakeFilePath), 0777, true);
        }
        file_put_contents($fakeFilePath, 'fake image content');

        // Act
        $response = $this->controller->show(
            PhotoType::SMALL->value,
            1,
            $photoRepository,
            $kernel,
            $photoService,
        );

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame('fake image content', $response->getContent());

        // Cleanup
        unlink($fakeFilePath);
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Photo), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresPhotoWhenValid(): void
    {
        // Arrange
        $photoFactory = $this->getMock(
            PhotoFactory::class,
            new AllowedMethod('create', $this->photo, $this->once(), [$this->photoValidation]),
        );
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->store(1, $recipeService, $photoFactory, $this->photoValidation);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertInstanceOf(RecipeResponse::class, $response);
    }
}
