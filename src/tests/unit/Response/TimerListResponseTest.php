<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\List\TimerList;
use App\Response\TimerListResponse;
use App\Tests\DataProvider\SimpleEntityDataProvider;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(TimerListResponse::class)]
class TimerListResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO na podstawie encji - Timer -> TimerList')]
    #[DataProviderExternal(SimpleEntityDataProvider::class, 'timersValues')]
    public function itCallsDtoFactoryWithCorrectParams(array $timers): void
    {
        // Arrange
        $this->setupFactoryForList(new TimerList(), [TimerList::class, ...$timers]);

        // Act
        new TimerListResponse($this->dtoFactory, ...$timers);
    }
}
