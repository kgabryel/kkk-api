<?php

namespace App\Tests\Helper;

use App\Tests\Helper\AllowedMethod\AllowedMethod;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use ReflectionClass;
use ReflectionMethod;

class SetupAllowedMethodsHelper
{
    private InvokedCount $never;

    public function __construct(InvokedCount $never)
    {
        $this->never = $never;
    }

    /**
     * @template T of object
     *
     * @param T&MockObject $mock
     * @param class-string<T> $className
     */
    public function setupAllowedMethods(MockObject $mock, string $className, AllowedMethod ...$allowedMethods): void
    {
        $methodsToSkip = array_map(
            static fn (AllowedMethod $allowedMethod): string => $allowedMethod->getMethodName(),
            $allowedMethods,
        );
        $methodsToSkip[] = '__construct';
        $methodsToSkip[] = '__clone';
        $reflection = new ReflectionClass($className);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();
            if (in_array($methodName, $methodsToSkip, true)) {
                continue;
            }
            if ($method->isFinal()) {
                continue;
            }
            $mock->expects($this->never)->method($methodName);
        }
        foreach ($allowedMethods as $allowedMethod) {
            if ($allowedMethod->getInvokedCount() instanceof InvocationOrder) {
                $tmp = $mock->expects($allowedMethod->getInvokedCount())->method($allowedMethod->getMethodName());
            } else {
                $tmp = $mock->method($allowedMethod->getMethodName());
            }
            if ($allowedMethod->getParameters() !== []) {
                $tmp->with(...$allowedMethod->getParameters());
            }
            if ($allowedMethod->isOverrideValue()) {
                if ($allowedMethod->isSimpleValue()) {
                    $tmp->willReturn($allowedMethod->getValue());
                } elseif ($allowedMethod->isMapValue()) {
                    $tmp->willReturnMap($allowedMethod->getValue());
                } else {
                    $tmp->willReturnCallback($allowedMethod->getValue());
                }
            }
            if (!$allowedMethod->getException() instanceof Exception) {
                continue;
            }
            $tmp->willThrowException($allowedMethod->getException());
        }
    }
}
