<?php

namespace App\Tests\Unit\Validation;

use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\Result;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

#[Small]
#[CoversClass(Result::class)]
class ResultTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Grupuje błędy walidacji według nazw pól i zwraca false gdy błędy istnieją')]
    public function itGroupsValidationErrorsByField(): void
    {
        // Arrange
        $violations = new ConstraintViolationList(
            [
                $this->prepareViolation('Error message 1', 'field1'),
                $this->prepareViolation('Error message 2', 'field2'),
                $this->prepareViolation('Another error for field1', 'field1'),
            ],
        );

        // Act
        $result = new Result($violations);

        // Prepare expected
        $expectedErrors = [
            'field1' => ['Error message 1', 'Another error for field1'],
            'field2' => ['Error message 2'],
        ];

        // Assert
        $this->assertFalse($result->passed());
        $this->assertSame($expectedErrors, $result->getErrors());
    }

    #[Test]
    #[TestDox('Zwraca true gdy nie ma błędów walidacji')]
    public function itReturnsTrueWhenNoValidationErrors(): void
    {
        // Arrange
        $emptyErrors = new ConstraintViolationList();

        // Act
        $result = new Result($emptyErrors);

        // Assert
        $this->assertTrue($result->passed());
        $this->assertSame([], $result->getErrors());
    }

    private function prepareViolation(string $errorMessage, string $field): ConstraintViolation
    {
        return new ConstraintViolation($errorMessage, null, [], '', $field, null);
    }
}
