<?php

namespace App\Tests\Integration\Service\Entity;

use App\Service\Entity\TagService;
use App\Service\UserService;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\TagValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[Medium]
#[CoversClass(TagService::class)]
class TagServiceTest extends BaseIntegrationTestCase
{
    private TagService $tagService;
    private TagValidation $tagValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $userService = $this->createStub(UserService::class);
        $userService->method('getUser')->willReturn($this->defaultUser);
        $container->set(UserService::class, $userService);
        $this->tagService = $container->get(TagService::class);
        $this->tagValidation = $container->get(TagValidation::class);
    }

    #[Test]
    #[TestDox('Pozwala dokanać aktualizacji z podaniem aktualnej nazwy')]
    public function itAllowsUpdatingWithSameName(): void
    {
        // Arrange
        $tag = EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => 'name']);
        $this->setRequestData(['name' => 'name']);

        // Act
        $result = $this->tagService->update($tag, $this->tagValidation);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('Odrzuca aktualizację przy duplikacji nazwy')]
    public function itRejectNameDuplicate(): void
    {
        // Arrange
        EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => 'name']);
        $tag2 = EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => 'name2']);
        $this->setRequestData(['name' => 'name']);

        // Act
        $result = $this->tagService->update($tag2, $this->tagValidation);

        // Assert
        $this->assertFalse($result);
    }

    private function setRequestData(array $data): void
    {
        $container = self::getContainer();
        $requestStack = $container->get(RequestStack::class);
        $request = $this->createStub(Request::class);
        $request->method('toArray')->willReturn($data);
        $requestStack->push($request);
    }
}
