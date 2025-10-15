<?php

namespace App\Tests\Unit\ValidatorRule\UniqueEmail;

use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\ValidatorRule\UniqueEmail\UniqueEmail;
use App\ValidatorRule\UniqueEmail\UniqueEmailValidator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[Small]
#[CoversClass(UniqueEmailValidator::class)]
#[CoversClass(UniqueEmail::class)]
class UniqueEmailValidatorTest extends BaseTestCase
{
    private UniqueEmail $constraint;
    private UniqueEmailValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UniqueEmailValidator();
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy nie istnieje standardowy użytkownik z tym emailem')]
    public function itDoesNotAddViolationWhenNoStandardUser(): void
    {
        // Arrange
        $this->init(
            $this->getMock(ExecutionContextInterface::class),
            $this->getMock(
                ServiceEntityRepository::class,
                new AllowedMethod(
                    'findOneBy',
                    null,
                    $this->once(),
                    [['email' => 'test@email.com', 'fbId' => null]],
                ),
            ),
        );

        // Act
        $this->validator->validate('test@email.com', $this->constraint);
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy istnieje standardowy użytkownik z tym emailem')]
    public function itFailsForExistingStandardUser(): void
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

        // Arrange
        $this->init(
            $context,
            $this->getMock(
                ServiceEntityRepository::class,
                new AllowedMethod(
                    'findOneBy',
                    EntityFactory::getSimpleUser(),
                    $this->once(),
                    [['email' => 'test@email.com', 'fbId' => null]],
                ),
            ),
        );

        // Act
        $this->validator->validate('test@email.com', $this->constraint);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy wartość jest pusta')]
    public function itSkipsEmptyValue(): void
    {
        // Arrange
        $this->init(
            $this->getMock(ExecutionContextInterface::class),
            $this->getMock(ServiceEntityRepository::class),
        );

        // Act
        $this->validator->validate(null, $this->constraint);
    }

    private function init(ExecutionContextInterface $context, ServiceEntityRepository $repository): void
    {
        $this->constraint = new UniqueEmail($repository);
        $this->validator->initialize($context);
    }
}
