<?php

namespace App\Tests\Integration\ValidatorRule\ExistsForUser;

use App\Repository\TagRepository;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\ValidatorRule\ExistsForUser\ExistsForUser;
use App\ValidatorRule\ExistsForUser\ExistsForUserValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(ExistsForUser::class)]
#[CoversClass(ExistsForUserValidator::class)]
class ExistsForUserValidatorTest extends BaseIntegrationTestCase
{
    private ExistsForUser $existsForUser;
    private TagRepository $tagRepository;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $this->validator = $container->get(ValidatorInterface::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->existsForUser = new ExistsForUser($this->tagRepository, $this->defaultUser);
    }

    #[Test]
    #[TestDox('Walidacja zwraca błąd, gdy encja nie należy do użytkownika lub nie istnieje')]
    #[DataProviderExternal(ValidationDataProvider::class, 'inaccessibleTagCases')]
    public function itFailsValidationWhenEntityMissing(
        string $tagToValidate,
        array $userTags,
        array $anotherUsersData,
    ): void {
        // Arrange
        foreach ($userTags as $tagName) {
            EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => $tagName]);
        }
        foreach ($anotherUsersData as $anotherUserData) {
            $anotherUser = EntityFactory::createUser($anotherUserData['email']);
            foreach ($anotherUserData['tags'] as $tagName) {
                EntityFactory::createTag($anotherUser->getEmail(), ['name' => $tagName]);
            }
        }
        $tag = $this->tagRepository->findOneBy(['name' => $tagToValidate, 'user' => $this->defaultUser]);
        $tagToValidateId = $tag?->getId() ?? 999999;

        // Act
        $violations = $this->validator->validate($tagToValidateId, $this->existsForUser);

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $violations,
            'No matching item found for this user.',
        );
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy encja należy do użytkownika')]
    public function itPassesValidationWhenEntityExists(): void
    {
        // Arrange
        $tag = EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => 'tagName']);

        // Act
        $violations = $this->validator->validate($tag->getId(), $this->existsForUser);

        // Assert
        $this->assertHasNoViolations($violations);
    }
}
