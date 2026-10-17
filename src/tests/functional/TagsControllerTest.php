<?php

namespace App\Tests\Functional;

use App\Controller\TagsController;
use App\Dto\Entity\Tag;
use App\Entity\Tag as TagEntity;
use App\Repository\TagRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ControllerDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\EntityTestDataDto;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Large]
#[CoversClass(TagsController::class)]
#[CoversClass(Tag::class)]
#[CoversClass(TagEntity::class)]
#[CoversClass(TagRepository::class)]
class TagsControllerTest extends BaseFunctionalTestCase
{
    private TagRepository $tagRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepository = self::getContainer()->get(TagRepository::class);
    }

    #[Test]
    #[TestDox('Usuwa encję (Tag) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserTagWhenAvailable(): void
    {
        // Arrange
        $tag = EntityFactory::createTag($this->user->getEmail());
        $tagId = $tag->getId();

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/tags/%s', $tagId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->tagRepository->find($tagId));
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może usunąć encji (Tag)')]
    public function itDeniesAccessToDeleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/tags/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać listy encji (Tag)')]
    public function itDeniesAccessToIndexWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/tags');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może utworzyć encji (Tag)')]
    public function itDeniesAccessToStoreWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/tags');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zaktulizować encji (Tag)')]
    public function itDeniesAccessToUpdateWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PUT', '/api/tags/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Tag) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsDeleteTagWhenUnavailable(array $items): void
    {
        // Arrange
        $tagId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): TagEntity => EntityFactory::createTag($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/tags/%s', $tagId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Tag) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Act
        $this->sendAuthorizedRequest('POST', '/api/tags', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji encji (Tag) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        $tag = EntityFactory::createTag($this->user->getEmail());

        // Act
        $this->sendAuthorizedRequest('PUT', sprintf('/api/tags/%s', $tag->getId()), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Nie aktualizuje encji (Tag) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsUpdateWhenTagUnavailable(array $items): void
    {
        // Arrange
        $tagId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): TagEntity => EntityFactory::createTag($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('PUT', sprintf('/api/tags/%s', $tagId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Tag) należących do bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'tagIndexData')]
    public function itReturnsOnlyUserTags(array $tags): void
    {
        // Arrange
        $expectedResponseData = $this->prepareExpectedIndexResponseData(
            $tags,
            static fn (EntityTestDataDto $tag): TagEntity => EntityFactory::createTag(
                $tag->getUserEmail(),
                $tag->getEntityData(),
            ),
            static fn (TagEntity $tag): array => [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
            ],
        );

        // Act
        $this->sendAuthorizedRequest('GET', '/api/tags', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($expectedResponseData, true);
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Tag), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresTagWhenValid(): void
    {
        // Act
        $this->sendAuthorizedJsonRequest('POST', '/api/tags', ['name' => 'tagName'], $this->token);

        // Prepare expected
        $createdTag = $this->getLastCreatedTag();
        $tagData = [
            'id' => $createdTag->getId(),
            'name' => $createdTag->getName(),
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponseEquals($tagData);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Tag), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itUpdatesTagWhenValid(): void
    {
        // Arrange
        $tag = EntityFactory::createTag($this->user->getEmail(), ['name' => 'name']);

        // Act
        $this->sendAuthorizedJsonRequest(
            'PUT',
            sprintf('/api/tags/%s', $tag->getId()),
            ['name' => 'new Name'],
            $this->token,
        );

        // Prepare expected
        $tagData = [
            'id' => $tag->getId(),
            'name' => 'NEW NAME',
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($tagData);
    }

    private function getLastCreatedTag(): TagEntity
    {
        return $this->tagRepository->findOneBy([], ['id' => 'DESC']);
    }
}
