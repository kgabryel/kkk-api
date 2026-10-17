<?php

namespace App\Tests\Unit\ValidatorRule\ExistsForUser;

use App\Entity\Tag;
use App\Entity\User;
use App\Repository\FindByIdInterface;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\ValidatorRule\ExistsForUser\ExistsForUser;
use App\ValidatorRule\ExistsForUser\ExistsForUserValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[Small]
#[CoversClass(ExistsForUserValidator::class)]
#[CoversClass(ExistsForUser::class)]
class ExistsForUserValidatorTest extends BaseTestCase
{
    private ExistsForUser $constraint;
    private Tag $tag;
    private User $user;
    private ExistsForUserValidator $validator;

    protected function setUp(): void
    {
        $this->tag = EntityFactory::getSimpleTag();
        $this->user = EntityFactory::getSimpleUser();
        $this->validator = new ExistsForUserValidator();
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy encja nie istnieje u użytkownika')]
    public function itFailsValidationWhenEntityMissing(): void
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
        $this->init(null, $context);

        // Act
        $this->validator->validate($this->tag->getId(), $this->constraint);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy encja istnieje u użytkownika')]
    public function itPassesValidationWhenEntityExists(): void
    {
        // Arrange
        $this->init($this->tag, $this->getMock(ExecutionContextInterface::class));

        // Act
        $this->validator->validate($this->tag->getId(), $this->constraint);
    }

    private function init(?Tag $returnValue, ExecutionContextInterface $context): void
    {
        $repository = $this->getMock(
            FindByIdInterface::class,
            new AllowedMethod(
                'findById',
                $returnValue,
                $this->once(),
                [$returnValue?->getId() ?? 1, $this->user],
            ),
        );
        $this->constraint = new ExistsForUser($repository, $this->user);
        $this->validator->initialize($context);
    }
}
