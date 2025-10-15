<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\List\SeasonList;
use App\Response\SeasonListResponse;
use App\Tests\DataProvider\SimpleEntityDataProvider;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(SeasonListResponse::class)]
class SeasonListResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO na podstawie encji - Season -> SeasonList')]
    #[DataProviderExternal(SimpleEntityDataProvider::class, 'seasonsValues')]
    public function itCallsDtoFactoryWithCorrectParams(array $seasons): void
    {
        // Arrange
        $this->setupFactoryForList(new SeasonList(), [SeasonList::class, ...$seasons]);

        // Act
        new SeasonListResponse($this->dtoFactory, ...$seasons);
    }
}
