<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\List\OzaSupplyList;
use App\Response\OzaSupplyListResponse;
use App\Tests\DataProvider\SimpleEntityDataProvider;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(OzaSupplyListResponse::class)]
class OzaSupplyListResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO na podstawie encji - OzaSupply -> OzaSupplyList')]
    #[DataProviderExternal(SimpleEntityDataProvider::class, 'ozaSuppliesValues')]
    public function itCallsDtoFactoryWithCorrectParams(array $ozaSupplies): void
    {
        // Arrange
        $this->setupFactoryForList(new OzaSupplyList(), [OzaSupplyList::class, ...$ozaSupplies]);

        // Act
        new OzaSupplyListResponse($this->dtoFactory, ...$ozaSupplies);
    }
}
