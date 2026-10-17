<?php

namespace App\Tests\Helper\TestCase;

use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\SetupAllowedMethodsHelper;
use App\Validation\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T&MockObject
     */
    protected function getMock(string $className, AllowedMethod ...$allowedMethods): object
    {
        $mock = $this->createMock($className);
        $this->setupAllowedMethods($mock, $className, ...$allowedMethods);

        return $mock;
    }

    protected function getValidationResult(bool $passed): Result
    {
        $result = $this->createStub(Result::class);
        $result->method('passed')->willReturn($passed);

        return $result;
    }

    /**
     * @template T of object
     *
     * @param T&MockObject $mock
     * @param class-string<T> $className
     */
    protected function setupAllowedMethods(MockObject $mock, string $className, AllowedMethod ...$allowedMethods): void
    {
        $setupAllowedMethodHelper = new SetupAllowedMethodsHelper($this->never());
        $setupAllowedMethodHelper->setupAllowedMethods($mock, $className, ...$allowedMethods);
    }
}
