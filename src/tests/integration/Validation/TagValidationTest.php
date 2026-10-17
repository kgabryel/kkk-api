<?php

namespace App\Tests\Integration\Validation;

use App\Repository\TagRepository;
use App\Service\UserService;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\TagValidation;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(TagValidation::class)]
#[CoversClass(UniqueNameForUser::class)]
class TagValidationTest extends BaseIntegrationTestCase
{
    private Request $request;
    private TagValidation $tagValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $validator = $container->get(ValidatorInterface::class);
        $tagRepository = $container->get(TagRepository::class);
        $this->request = $this->createStub(Request::class);
        $requestStack = new RequestStack([$this->request]);
        $userService = $this->createStub(UserService::class);
        $userService->method('getUser')->willReturn($this->defaultUser);
        $this->tagValidation = new TagValidation(
            $validator,
            $requestStack,
            $userService,
            $tagRepository,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy nazwa jest już wykorzystywana')]
    public function itFailsWhenNameIsAlreadyUsed(): void
    {
        // Arrange
        EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => 'name']);
        $this->request->method('toArray')->willReturn(['name' => 'name']);

        // Act
        $result = $this->tagValidation->validate();

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $result->getRawErrors(),
            'This name is already used.',
        );
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy nazwa nie jest wykorzystywana')]
    public function itPassesWhenNameIsUnused(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(['name' => 'name']);

        // Act
        $result = $this->tagValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy nazwa jest używana przez innego użytkownika')]
    public function itPassesWhenNameIsUsedByAnotherUser(): void
    {
        // Arrange
        EntityFactory::createTag(EntityFactory::USER_EMAIL_2, ['name' => 'name']);
        $this->request->method('toArray')->willReturn(['name' => 'name']);

        // Act
        $result = $this->tagValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy nazwa jest używana przy edycji tego samego zasobu')]
    public function itPassesWhenNameIsUsedInEditMode(): void
    {
        // Arrange
        $tag = EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => 'name']);
        $this->request->method('toArray')->willReturn(['name' => 'name']);
        $this->tagValidation->setExpect($tag->getId());

        // Act
        $result = $this->tagValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }
}
