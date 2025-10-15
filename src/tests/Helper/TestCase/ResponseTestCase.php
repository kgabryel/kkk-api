<?php

namespace App\Tests\Helper\TestCase;

use App\Factory\DtoFactoryDispatcher;
use App\Tests\Helper\AllowedMethod\AllowedMethod;

class ResponseTestCase extends BaseTestCase
{
    protected DtoFactoryDispatcher $dtoFactory;

    protected function setupFactoryForList(mixed $returnValue, array $params): void
    {
        $this->dtoFactory = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('getMany', $returnValue, $this->once(), $params),
        );
    }

    protected function setupFactoryForSingleEntity(array $params): void
    {
        $this->dtoFactory = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('get', invokedCount: $this->once(), parameters: $params, overrideValue: false),
        );
    }
}
