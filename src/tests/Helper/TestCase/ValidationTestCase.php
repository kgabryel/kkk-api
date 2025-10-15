<?php

namespace App\Tests\Helper\TestCase;

use App\Service\UserService;
use App\Tests\Helper\EntityFactory;
use App\Validation\Result;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationTestCase extends BaseTestCase
{
    protected Request&Stub $request;
    protected RequestStack $requestStack;
    protected UserService $userService;
    protected ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
        $this->request = $this->createStub(Request::class);
        $this->requestStack = new RequestStack([$this->request]);
        $this->userService = $this->createStub(UserService::class);
        $this->userService->method('getUser')->willReturn(EntityFactory::getSimpleUser());
    }

    protected function assertFieldHasOnlyOneErrorWithMessage(
        Result $result,
        string $field,
        ?string $expectedMessage,
    ): void {
        $errors = $result->getErrors();
        $fieldErrors = $errors[$field] ?? [];
        $this->assertSame(
            [$field],
            array_keys($errors),
            "Expected error only in field '{$field}', but got errors in fields: [" .
            implode(', ', array_keys($errors)) .
            ']',
        );
        $this->assertCount(
            1,
            $fieldErrors,
            "Expected exactly one error on field '{$field}', got " . count($fieldErrors),
        );
        if ($expectedMessage === null) {
            return;
        }

        $this->assertSame(
            $expectedMessage,
            $fieldErrors[0],
            "Expected error message '{$expectedMessage}' on field '{$field}', got '{$fieldErrors[0]}'",
        );
    }

    protected function assertFieldIsValid(Result $result, string $field): void
    {
        $errors = $result->getErrors();
        $this->assertArrayNotHasKey($field, $errors);
    }

    protected function assertHasExtraFieldError(Result $result): void
    {
        $errors = $result->getErrors();
        $this->assertArrayHasKey(
            '[extra_field]',
            $errors,
            'Expected validation errors to contain [extra_field].',
        );
    }
}
