<?php

namespace App\Tests\Unit\Service\Entity;

use App\Dto\Request\Tag;
use App\Entity\Tag as TagEntity;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Service\Entity\TagService;
use App\Service\UserService;
use App\Tests\DataProvider\FindEntityDataProvider;
use App\Tests\DataProvider\UpdateEntityDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\TagValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(TagService::class)]
#[CoversClass(Tag::class)]
class TagServiceTest extends BaseTestCase
{
    private TagEntity $tag;
    private TagService $tagService;
    private User $user;

    protected function setUp(): void
    {
        $this->tag = EntityFactory::getSimpleTag();
        $this->user = EntityFactory::getSimpleUser();
    }

    #[Test]
    #[TestDox('Zwraca encję (Tag) użytkownika znalezioną w repozytorium')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'tagValues')]
    public function itFindsTag(int $id, ?TagEntity $tag): void
    {
        // Arrange
        $this->init(tagRepository: $this->getMock(
            TagRepository::class,
            new AllowedMethod('findById', $tag, $this->once(), [$id, $this->user]),
        ));

        // Act
        $result = $this->tagService->find($id);

        // Assert
        $this->assertSame($tag, $result);
    }

    #[Test]
    #[TestDox('Odrzuca aktualizację encji (Tag), gdy formularz jest niepoprawny')]
    public function itRejectsInvalidForm(): void
    {
        // Arrange
        $tagClone = clone $this->tag;
        $this->init($this->getMock(EntityManagerInterface::class));
        $tagValidation = $this->getMock(
            TagValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
            new AllowedVoidMethod('setExpect', $this->once(), [$this->tag->getId()]),
        );

        // Act
        $result = $this->tagService->update($this->tag, $tagValidation);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals($tagClone, $this->tag);
    }

    #[Test]
    #[TestDox('Usuwa encję (Tag) z bazy danych')]
    public function itRemovesTag(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('remove', $this->once(), [$this->tag]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->tagService->remove($this->tag);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Tag), gdy formularz jest poprawny')]
    #[DataProviderExternal(UpdateEntityDataProvider::class, 'tagValues')]
    public function itUpdatesTagWhenFormIsValid(string $name): void
    {
        // Arrange
        $tagClone = clone $this->tag;
        $tagClone->setName($name);
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('persist', $this->once(), [$this->tag]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );
        $timerValidation = $this->getMock(
            TagValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new Tag($name), $this->once()),
            new AllowedVoidMethod('setExpect', $this->once(), [$this->tag->getId()]),
        );

        // Act
        $result = $this->tagService->update($this->tag, $timerValidation);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals($tagClone, $this->tag);
    }

    private function init(?EntityManagerInterface $entityManager = null, ?TagRepository $tagRepository = null): void
    {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->tagService = new TagService(
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $userService,
            $tagRepository ?? $this->createStub(TagRepository::class),
        );
    }
}
