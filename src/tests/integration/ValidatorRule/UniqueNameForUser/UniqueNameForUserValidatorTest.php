<?php

namespace App\Tests\Integration\ValidatorRule\UniqueNameForUser;

use App\Repository\TagRepository;
use App\Tests\DataProvider\TagDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUserValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(UniqueNameForUser::class)]
#[CoversClass(UniqueNameForUserValidator::class)]
class UniqueNameForUserValidatorTest extends BaseIntegrationTestCase
{
    private TagRepository $tagRepository;
    private UniqueNameForUser $uniqueNameForUser;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $this->validator = $container->get(ValidatorInterface::class);
        $this->tagRepository = $container->get(TagRepository::class);
        $this->uniqueNameForUser = new UniqueNameForUser($this->tagRepository, $this->defaultUser, 'name');
    }

    #[Test]
    #[TestDox('Nie zwraca błędu, gdy nazwa należy do innego użytkownika')]
    #[DataProviderExternal(TagDataProvider::class, 'caseInsensitiveNameValues')]
    public function itAcceptsNameUsedByAnotherUser(string $existingName, string $testingName): void
    {
        // Arrange
        EntityFactory::createTag(EntityFactory::USER_EMAIL_2, ['name' => $existingName]);

        // Act
        $violations = $this->validator->validate($testingName, $this->uniqueNameForUser);

        // Assert
        $this->assertHasNoViolations($violations);
    }

    #[Test]
    #[TestDox('Nie zwraca błędu, gdy użytkownik pokonuje edycji i pozostawia tę samą nazwę')]
    #[DataProviderExternal(TagDataProvider::class, 'caseInsensitiveNameValues')]
    public function itAcceptsUnchangedNameDuringEdit(string $existingName, string $testingName): void
    {
        // Arrange
        $tag = EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => $existingName]);
        $this->uniqueNameForUser = new UniqueNameForUser(
            $this->tagRepository,
            $this->defaultUser,
            'name',
            $tag->getId(),
        );

        // Act
        $violations = $this->validator->validate($testingName, $this->uniqueNameForUser);

        // Assert
        $this->assertHasNoViolations($violations);
    }

    #[Test]
    #[TestDox('Nie zwraca błędu, gdy nazwa nie została jeszcze użyta przez użytkownika')]
    #[DataProviderExternal(ValidationDataProvider::class, 'uniqueTagNameCases')]
    public function itAcceptsUniqueNameForUser(array $existing, string $testing): void
    {
        // Arrange
        foreach ($existing as $name) {
            EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => $name]);
        }

        // Act
        $violations = $this->validator->validate($testing, $this->uniqueNameForUser);

        // Assert
        $this->assertHasNoViolations($violations);
    }

    #[Test]
    #[TestDox('Zwraca błąd, gdy nazwa już istnieje dla tego samego użytkownika (ignorując wielkość liter)')]
    #[DataProviderExternal(TagDataProvider::class, 'caseInsensitiveNameValues')]
    public function itRejectsDuplicateNameForSameUser(string $existing, string $testing): void
    {
        // Arrange
        EntityFactory::createTag($this->defaultUser->getEmail(), ['name' => $existing]);

        // Act
        $violations = $this->validator->validate($testing, $this->uniqueNameForUser);

        // Assert
        $this->assertHasOnlyOneViolationWithMessage($violations, 'This name is already used.');
    }
}
