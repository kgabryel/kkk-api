<?php

namespace App\Tests\Helper\AllowedMethod;

use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class AllowedVoidMethod extends AllowedMethod
{
    public function __construct(string $methodName, ?InvocationOrder $invokedCount = null, array $parameters = [])
    {
        parent::__construct(
            methodName: $methodName,
            invokedCount: $invokedCount,
            parameters: $parameters,
            overrideValue: false,
        );
    }
}
