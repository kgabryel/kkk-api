<?php

namespace App\Tests\Helper\AllowedMethod;

use Exception;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class AllowedExceptionMethod extends AllowedMethod
{
    public function __construct(string $method, InvocationOrder $invocationOrder, Exception $exception)
    {
        parent::__construct($method, invokedCount: $invocationOrder, overrideValue: false, exception: $exception);
    }
}
