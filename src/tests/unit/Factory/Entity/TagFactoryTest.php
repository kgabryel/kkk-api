<?php

namespace App\Tests\Unit\Factory\Entity;

use App\Dto\Request\Tag as TagRequest;
use App\Entity\Tag;
use App\Entity\User;
use App\Factory\Entity\TagFactory;
use App\Service\UserService;
use App\Tests\DataProvider\EntityFactoryDataProvider;
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
#[CoversClass(TagFactory::class)]
#[CoversClass(Tag::class)]
#[CoversClass(TagRequest::class)]
class TagFactoryTest extends BaseTestCase
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
    #[TestDox('Tworzy encję (Tag), gdy walidacja przeszła pomyślnie')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'tagNameValues')]
    public function itCreatesTagOnValidInput(string $tagName): void
    {
        // Arrange
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod(
                'persist',
                $this->once(),
                [$this->callback($this->tagMatcher($tagName))],
            ),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $factory = new TagFactory($entityManager, $this->userService);
        $tagModel = new TagRequest($tagName);
        $tagValidation = $this->getMock(
            TagValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $tagModel, $this->once()),
        );

        // Act
        $tag = $factory->create($tagValidation);

        // Assert
        $this->assertInstanceOf(Tag::class, $tag);
    }

    #[Test]
    #[TestDox('Odrzuca dane i przerywa tworzenie, gdy walidacja się nie powiodła')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $factory = new TagFactory($this->getMock(EntityManagerInterface::class), $this->userService);
        $tagValidation = $this->getMock(
            TagValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $tag = $factory->create($tagValidation);

        // Assert
        $this->assertNull($tag);
    }

    private function tagMatcher(string $tagName): callable
    {
        $user = $this->user;

        return static function ($tag) use ($user, $tagName): bool {
            return $tag instanceof Tag
                && $tag->getName() === strtoupper($tagName)
                && $tag->getUser() === $user;
        };
    }
}
