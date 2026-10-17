<?php

namespace App\Tests\Helper\AllowedMethod;

use Exception;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class AllowedMethod
{
    private ?Exception $exception;
    private ?InvocationOrder $invokedCount;
    private bool $mapValue;
    private string $methodName;
    private bool $overrideValue;
    private array $parameters;
    private bool $simpleValue;
    private mixed $value;

    public function __construct(
        string $methodName,
        mixed $value = null,
        ?InvocationOrder $invokedCount = null,
        array $parameters = [],
        bool $overrideValue = true,
        bool $simpleValue = true,
        ?Exception $exception = null,
        bool $mapValue = false,
    ) {
        $this->methodName = $methodName;
        $this->invokedCount = $invokedCount;
        $this->overrideValue = $overrideValue;
        if ($overrideValue) {
            $this->value = $value;
            $this->simpleValue = $simpleValue;
            $this->mapValue = $mapValue;
        }
        $this->parameters = $parameters;
        $this->exception = $exception;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }

    public function getInvokedCount(): ?InvocationOrder
    {
        return $this->invokedCount;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isMapValue(): bool
    {
        return $this->mapValue;
    }

    public function isOverrideValue(): bool
    {
        return $this->overrideValue;
    }

    public function isSimpleValue(): bool
    {
        return $this->simpleValue;
    }
}
