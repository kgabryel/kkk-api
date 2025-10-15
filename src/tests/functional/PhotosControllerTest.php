<?php

namespace App\Tests\Functional;

use App\Config\PhotoType;
use App\Controller\PhotosController;
use App\Dto\Entity\Photo;
use App\Entity\Photo as PhotoEntity;
use App\Entity\Recipe;
use App\Repository\PhotoRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\FixtureHelper;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Large]
#[CoversClass(PhotosController::class)]
#[CoversClass(PhotoEntity::class)]
#[CoversClass(Photo::class)]
class PhotosControllerTest extends BaseFunctionalTestCase
{
    private PhotoRepository $photoRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->photoRepository = self::getContainer()->get(PhotoRepository::class);
    }

    #[Test]
    #[TestDox('Usuwa encję (Photo) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserPhotoWhenAvailable(): void
    {
        // Arrange
        $photo = self::createPhoto($this->user->getEmail());
        $photoId = $photo->getId();
        self::createPhoto($this->user->getEmail(), ['recipe' => $photo->getRecipe()]);

        // Act
        $this->sendAuthorizedRequest(
            'DELETE',
            sprintf('/api/recipes/1/photos/%s', $photoId),
            $this->token,
        );

        // Prepare expected
        $response = $this->getResponseContent();
        $values = array_map(static fn (array $el) => $el['id'], $response['photos']);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertFileDoesNotExist(self::getPhotoPath(PhotoType::ORIGINAL, $photo->getFileName()));
        self::assertFileDoesNotExist(self::getPhotoPath(PhotoType::MEDIUM, $photo->getFileName()));
        self::assertFileDoesNotExist(self::getPhotoPath(PhotoType::SMALL, $photo->getFileName()));
        $this->assertNotContains($photoId, $values);
        $this->assertNull($this->photoRepository->find($photoId));
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może usunąć encji (Photo)')]
    public function itDeniesAccessToDeleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/recipes/1/photos/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie zmienić kolejności zdjęć')]
    public function itDeniesAccessToReorderPhotosWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PATCH', '/api/recipes/1/photos');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może utworzyć encji (Photo)')]
    public function itDeniesAccessToStoreWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/recipes/1/photos');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Photo) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsDeletePhotoWhenUnavailable(array $items): void
    {
        // Arrange
        $photoId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): PhotoEntity => self::createPhoto($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest(
            'DELETE',
            sprintf('/api/recipes/1/photos/%s', $photoId),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie zmiany kolejności zdjęć i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnReorderPhotos(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe($this->user->getEmail());

        // Act
        $this->sendAuthorizedRequest(
            'PATCH',
            sprintf('/api/recipes/%s/photos', $recipe->getId()),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie zmiany kolejności zdjęć i zwraca 404, gdy przepis jest niedostępny')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsReorderPhotosWhenRecipeUnavailable(array $items): void
    {
        // Arrange
        $recipeId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): Recipe => EntityFactory::createRecipe($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest(
            'PATCH',
            sprintf('/api/recipes/%s/photos', $recipeId),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utworzenia encji (Photo) i zwraca 404, gdy przepis jest niedostępny')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsStoreWhenRecipeUnavailable(array $items): void
    {
        // Arrange
        $recipeId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): Recipe => EntityFactory::createRecipe($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest(
            'POST',
            sprintf('/api/recipes/%s/photos', $recipeId),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zmienia kolejność zdjęć w przepisie')]
    public function itReorderPhotos(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe($this->user->getEmail());
        $request = ['order' => []];
        $photo = self::createPhoto($this->user->getEmail(), ['recipe' => $recipe, 'photo_order' => 999]);
        $recipe->addPhoto($photo);
        $request['order'][] = ['id' => $photo->getId(), 'index' => 1];
        $photo2 = self::createPhoto($this->user->getEmail(), ['recipe' => $recipe, 'photo_order' => 998]);
        $recipe->addPhoto($photo);
        $request['order'][] = ['id' => $photo2->getId(), 'index' => 3];
        $photo3 = self::createPhoto($this->user->getEmail(), ['recipe' => $recipe, 'photo_order' => 996]);
        $recipe->addPhoto($photo);
        $request['order'][] = ['id' => $photo3->getId(), 'index' => 2];

        // Act
        $this->sendAuthorizedJsonRequest(
            'PATCH',
            sprintf('/api/recipes/%s/photos', $recipe->getId()),
            $request,
            $this->token,
        );

        // Prepare expected
        $expected = array_map(
            static fn ($photo): array => [
                'height' => $photo->getHeight(),
                'id' => $photo->getId(),
                'type' => $photo->getType(),
                'width' => $photo->getWidth(),
            ],
            [$photo, $photo3, $photo2],
        );
        $response = $this->getResponseContent();

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($expected, $response['photos']);
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy zdjęcie nie istnieje lub nie jest publiczne')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itReturnsForbiddenIfPhotoNotFound(array $items): void
    {
        // Arrange
        $photoToFind = null;
        foreach ($items as $photo) {
            $photoEmail = $photo['email'];
            $toFind = $photo['toFind'] ?? false;
            unset($photo['email'], $photo['toFind']);
            $recipe = EntityFactory::createRecipe($photoEmail, ['public' => false]);
            $photo['recipe'] = $recipe;
            $createdTag = self::createPhoto($photoEmail, $photo);
            if (!$toFind) {
                continue;
            }
            $photoToFind = $createdTag;
        }
        $photoId = $photoToFind?->getId() ?? 999999;
        $recipeId = $photoToFind?->getRecipe()?->getId() ?? 999999;

        // Act
        $this->sendAuthorizedRequest(
            'GET',
            sprintf('/api/recipes/%s/photos/%s/medium', $recipeId, $photoId),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy użytkownik nie ma dostępu do zdjęcia')]
    public function itReturnsForbiddenWhenNoPhotoAccess(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe(EntityFactory::USER_EMAIL_2, ['public' => false]);
        $photo = self::createPhoto(EntityFactory::USER_EMAIL_2, ['recipe' => $recipe]);

        // Act
        $this->sendAuthorizedRequest(
            'GET',
            sprintf('/api/recipes/%s/photos/%s/medium', $photo->getRecipe()->getId(), $photo->getId()),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    #[TestDox('Zwraca zdjęcie i 200, gdy użytkownik ma dostęp - medium')]
    public function itReturnsMediumPhotoIfUserHasAccess(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe(EntityFactory::USER_EMAIL_2, ['public' => true]);
        $photo = self::createPhoto(
            EntityFactory::USER_EMAIL_2,
            ['type' => 'image/png', 'recipe' => $recipe],
            '-file-content',
        );

        // Act
        $this->sendAuthorizedRequest(
            'GET',
            sprintf('/api/recipes/%s/photos/%s/medium', $photo->getRecipe()->getId(), $photo->getId()),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('medium-file-content', $this->getPhotoContent());
        self::assertSame('image/png', $this->client->getResponse()->headers->get('content-type'));
    }

    #[Test]
    #[TestDox('Zwraca zdjęcie i 200, gdy użytkownik ma dostęp - original')]
    public function itReturnsOriginalPhotoIfUserHasAccess(): void
    {
        // Arrange
        $photo = self::createPhoto($this->user->getEmail(), ['type' => 'image/png'], '-file-content');

        // Act
        $this->sendAuthorizedRequest(
            'GET',
            sprintf('/api/recipes/%s/photos/%s/original', $photo->getRecipe()->getId(), $photo->getId()),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('original-file-content', $this->getPhotoContent());
        self::assertSame('image/png', $this->client->getResponse()->headers->get('content-type'));
    }

    #[Test]
    #[TestDox('Zwraca zdjęcie i 200, gdy użytkownik ma dostęp - small')]
    public function itReturnsSmallPhotoIfUserHasAccess(): void
    {
        // Arrange
        $photo = self::createPhoto(
            $this->user->getEmail(),
            ['type' => 'image/png'],
            '-file-content',
        );

        // Act
        $this->sendAuthorizedRequest(
            'GET',
            sprintf('/api/recipes/%s/photos/%s/small', $photo->getRecipe()->getId(), $photo->getId()),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('small-file-content', $this->getPhotoContent());
        self::assertSame('image/png', $this->client->getResponse()->headers->get('content-type'));
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Photo), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresPhotosWhenValid(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe($this->user->getEmail());
        $photoContent = FixtureHelper::getFixture('image_base64.txt');
        $photo2 = self::createPhoto(
            $this->user->getEmail(),
            ['recipe' => $recipe, 'height' => 100, 'type' => 'image/jpg', 'width' => 100],
        );

        // Act
        $this->sendAuthorizedJsonRequest(
            'POST',
            sprintf('/api/recipes/%s/photos', $recipe->getId()),
            ['photo' => $photoContent],
            $this->token,
        );

        // Prepare expected
        $photo = $this->getLastCreatedPhoto();
        $response = $this->getResponseContent();
        $expected = [
            [
                'height' => 100,
                'id' => $photo2->getId(),
                'type' => 'image/jpg',
                'width' => 100,
            ],
            [
                'height' => 600,
                'id' => $photo->getId(),
                'type' => 'image/png',
                'width' => 800,
            ],
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertFileExists(self::getPhotoPath(PhotoType::ORIGINAL, $photo->getFileName()));
        self::assertFileExists(self::getPhotoPath(PhotoType::MEDIUM, $photo->getFileName()));
        self::assertFileExists(self::getPhotoPath(PhotoType::SMALL, $photo->getFileName()));
        self::assertSame($expected, $response['photos']);
    }

    private function getLastCreatedPhoto(): PhotoEntity
    {
        return $this->photoRepository->findOneBy([], ['id' => 'DESC']);
    }

    private function getPhotoContent(): string
    {
        return $this->client->getResponse()->getContent();
    }
}
