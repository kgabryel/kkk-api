<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\Timer;
use App\Response\TimerResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(TimerResponse::class)]
class TimerResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - Timer')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $timer = EntityFactory::getSimpleTimer();
        $this->setupFactoryForSingleEntity([Timer::class, $timer]);

        // Act
        new TimerResponse($this->dtoFactory, $timer, Response::HTTP_OK);
    }
}
