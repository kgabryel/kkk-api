<?php

namespace App\Tests\Unit\ValidatorRule\UniqueNameForUser;

use App\Entity\Tag;
use App\Entity\User;
use App\Repository\FindOneByNameWithLowercaseInterface;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUserValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[Small]
#[CoversClass(UniqueNameForUserValidator::class)]
#[CoversClass(UniqueNameForUser::class)]
class UniqueNameForUserValidatorTest extends BaseTestCase
{
    private UniqueNameForUser $constraint;
    private Tag $entity;
    private User $user;
    private UniqueNameForUserValidator $validator;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->entity = EntityFactory::getSimpleTag();
        $this->validator = new UniqueNameForUserValidator();
    }

    #[Test]
    #[TestDox('Akceptuje unikalną nazwę')]
    public function itDoesNotAddErrorWhenNameIsUnique(): void
    {
        // Arrange
        $repository = $this->getMock(
            FindOneByNameWithLowercaseInterface::class,
            new AllowedMethod(
                'findOneByNameWithLowercase',
                null,
                $this->once(),
                [$this->user, 'column', 'name'],
            ),
        );
        $this->init($this->getMock(ExecutionContextInterface::class), $repository);

        // Act
        $this->validator->validate('name', $this->constraint);
    }

    #[Test]
    #[TestDox('Odrzuca nazwę, gdy ID encji nie zgadza się z oczekiwanym')]
    public function itFailsWhenEntityIdMismatch(): void
    {
        // Arrange
        $violationBuilder = $this->getMock(
            ConstraintViolationBuilderInterface::class,
            new AllowedVoidMethod('addViolation', $this->once()),
        );
        $context = $this->getMock(
            ExecutionContextInterface::class,
            new AllowedMethod('buildViolation', $violationBuilder, $this->once()),
        );
        $repository = $this->getMock(
            FindOneByNameWithLowercaseInterface::class,
            new AllowedMethod(
                'findOneByNameWithLowercase',
                $this->entity,
                $this->once(),
                [$this->user, 'column', 'name'],
            ),
        );
        $this->init($context, $repository, 3);

        // Act
        $this->validator->validate('name', $this->constraint);
    }

    #[Test]
    #[TestDox('Akceptuje nazwę, gdy ID encji zgadza się z oczekiwanym')]
    public function itSkipsErrorWhenEntityIdMatches(): void
    {
        // Arrange
        $repository = $this->getMock(
            FindOneByNameWithLowercaseInterface::class,
            new AllowedMethod(
                'findOneByNameWithLowercase',
                $this->entity,
                $this->once(),
                [$this->user, 'column', 'name'],
            ),
        );
        $this->init($this->getMock(ExecutionContextInterface::class), $repository, $this->entity->getId());

        // Act
        $this->validator->validate('name', $this->constraint);
    }

    #[Test]
    #[TestDox('Ignoruje wartość, gdy nie jest poprawnym niepustym stringiem')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itSkipsValidationWhenValueInvalid(mixed $value): void
    {
        // Arrange
        $this->init(
            $this->getMock(ExecutionContextInterface::class),
            $this->getMock(FindOneByNameWithLowercaseInterface::class),
        );

        // Act
        $this->validator->validate($value, $this->constraint);
    }

    private function init(
        ExecutionContextInterface $context,
        FindOneByNameWithLowercaseInterface $repository,
        int $expect = 0,
    ): void {
        $this->constraint = new UniqueNameForUser($repository, $this->user, 'column', $expect);
        $this->validator->initialize($context);
    }
}
