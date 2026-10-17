<?php

namespace App\Tests\Unit\ValidatorRule\PasswordsMatch;

use App\Tests\DataProvider\PasswordDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\ValidatorRule\PasswordsMatch\PasswordsMatch;
use App\ValidatorRule\PasswordsMatch\PasswordsMatchValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

#[Small]
#[CoversClass(PasswordsMatchValidator::class)]
#[CoversClass(PasswordsMatch::class)]
class PasswordsMatchValidatorTest extends BaseTestCase
{
    private PasswordsMatch $constraint;
    private PasswordsMatchValidator $validator;

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy hasła się różnią')]
    public function itFailsValidationWhenPasswordsMismatch(): void
    {
        // Arrange
        $violationBuilder = $this->getMock(
            ConstraintViolationBuilderInterface::class,
            new AllowedMethod('addViolation', invokedCount: $this->once(), overrideValue: false),
        );
        $this->init(
            $this->getMock(
                ExecutionContextInterface::class,
                new AllowedMethod('getRoot', ['password' => ['first' => 'abc']]),
                new AllowedMethod('buildViolation', $violationBuilder, $this->once()),
                new AllowedMethod('getViolations', overrideValue: false),
            ),
        );

        // Act
        $this->validator->validate('password', $this->constraint);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy hasła się zgadzają')]
    public function itPassesValidationWhenPasswordsMatch(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                ExecutionContextInterface::class,
                new AllowedMethod('getRoot', ['password' => ['first' => 'abc']]),
                new AllowedMethod('getViolations', new ConstraintViolationList([]), $this->once()),
            ),
        );

        // Act
        $this->validator->validate('abc', $this->constraint);
    }

    #[Test]
    #[TestDox('Nie porównuje haseł, gdy pierwsze powtórzenie jest puste')]
    #[DataProviderExternal(PasswordDataProvider::class, 'emptyPasswordValues')]
    public function itSkipsValidationIfFirstPasswordEmpty(array $data): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                ExecutionContextInterface::class,
                new AllowedMethod('getRoot', $data),
                new AllowedMethod('getViolations', new ConstraintViolationList([]), $this->once()),
            ),
        );

        // Act
        $this->validator->validate('password', $this->constraint);
    }

    #[Test]
    #[TestDox('Nie porównuje haseł, gdy pierwsze powtórzenie jest błędne')]
    public function itSkipsValidationIfFirstRepetitionIsInvalid(): void
    {
        // Arrange
        $constraintViolation = $this->getMock(
            ConstraintViolationInterface::class,
            new AllowedMethod('getPropertyPath', '[password][first]'),
        );
        $this->init(
            $this->getMock(
                ExecutionContextInterface::class,
                new AllowedMethod(
                    'getViolations',
                    new ConstraintViolationList([$constraintViolation]),
                    $this->once(),
                ),
            ),
        );

        // Act
        $this->validator->validate('password', $this->constraint);
    }

    private function init(ExecutionContextInterface $context): void
    {
        $this->validator = new PasswordsMatchValidator();
        $this->validator->initialize($context);
        $this->constraint = new PasswordsMatch('password');
    }
}
