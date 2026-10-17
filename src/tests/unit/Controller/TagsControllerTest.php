<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TagsController;
use App\Dto\Entity\List\TagList;
use App\Dto\Entity\Tag;
use App\Entity\Tag as TagEntity;
use App\Factory\DtoFactoryDispatcher;
use App\Factory\Entity\TagFactory;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Response\TagResponse;
use App\Service\Entity\TagService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\TagValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(TagsController::class)]
class TagsControllerTest extends BaseTestCase
{
    private TagsController $controller;
    private TagEntity $tag;
    private TagValidation $tagValidation;

    protected function setUp(): void
    {
        $this->tag = EntityFactory::getSimpleTag();
        $this->tagValidation = $this->createStub(TagValidation::class);
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('getMany', new TagList()),
            new AllowedMethod('get', $this->createStub(Tag::class)),
        );
        $userRepository = $this->createStub(UserRepository::class);
        $this->controller = $this->getMockBuilder(TagsController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn(EntityFactory::getSimpleUser());
    }

    #[Test]
    #[TestDox('Usuwa encję (Tag) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserTagWhenAvailable(): void
    {
        // Arrange
        $tagService = $this->getMock(
            TagService::class,
            new AllowedMethod('find', $this->tag, $this->once(), [1]),
            new AllowedVoidMethod('remove', $this->once(), [$this->tag]),
        );

        // Act
        $response = $this->controller->destroy(1, $tagService);

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Tag) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsDeleteTagWhenUnavailable(): void
    {
        // Arrange
        $tagService = $this->getMock(
            TagService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->destroy(1, $tagService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Tag) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Arrange
        $tagFactory = $this->getMock(
            TagFactory::class,
            new AllowedMethod('create', null, $this->once(), [$this->tagValidation]),
        );

        // Act
        $response = $this->controller->store($tagFactory, $this->tagValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji encji (Tag) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        $tagService = $this->getMock(
            TagService::class,
            new AllowedMethod('find', $this->tag, $this->once(), [1]),
            new AllowedMethod('update', false, $this->once(), [$this->tag, $this->tagValidation]),
        );

        // Act
        $response = $this->controller->update(1, $this->tagValidation, $tagService);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie aktualizuje encji (Tag) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsUpdateWhenTagUnavailable(): void
    {
        // Arrange
        $tagService = $this->getMock(
            TagService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->update(1, $this->tagValidation, $tagService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Tag) należących do bieżącego użytkownika')]
    public function itReturnsOnlyUserTags(): void
    {
        // Arrange
        $tagRepository = $this->getMock(
            TagRepository::class,
            new AllowedMethod('findForUser', invokedCount: $this->once(), overrideValue: false),
        );

        // Act
        $response = $this->controller->index($tagRepository);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Tag), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresTagWhenValid(): void
    {
        // Arrange
        $tagFactory = $this->getMock(
            TagFactory::class,
            new AllowedMethod('create', $this->tag, $this->once(), [$this->tagValidation]),
        );

        // Act
        $response = $this->controller->store($tagFactory, $this->tagValidation);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertInstanceOf(TagResponse::class, $response);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Tag), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itUpdatesTagWhenValid(): void
    {
        // Arrange
        $tagService = $this->getMock(
            TagService::class,
            new AllowedMethod('find', $this->tag, $this->once(), [1]),
            new AllowedMethod('update', true, $this->once(), [$this->tag, $this->tagValidation]),
        );

        // Act
        $response = $this->controller->update(1, $this->tagValidation, $tagService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(TagResponse::class, $response);
    }
}
