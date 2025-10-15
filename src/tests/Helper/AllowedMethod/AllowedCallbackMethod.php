<?php

namespace App\Tests\Helper\AllowedMethod;

use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class AllowedCallbackMethod extends AllowedMethod
{
    public function __construct(string $methodName, callable $value, ?InvocationOrder $invokedCount = null)
    {
        parent::__construct(methodName: $methodName, value: $value, invokedCount: $invokedCount, simpleValue: false);
    }
}
