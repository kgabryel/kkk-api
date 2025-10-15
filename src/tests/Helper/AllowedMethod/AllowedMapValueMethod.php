<?php

namespace App\Tests\Helper\AllowedMethod;

use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

class AllowedMapValueMethod extends AllowedMethod
{
    public function __construct(
        string $methodName,
        array $value,
        ?InvocationOrder $invokedCount = null,
        array $parameters = [],
    ) {
        parent::__construct($methodName, $value, $invokedCount, $parameters, simpleValue: false, mapValue: true);
    }
}
