<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\List\TagList;
use App\Response\TagListResponse;
use App\Tests\DataProvider\SimpleEntityDataProvider;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(TagListResponse::class)]
class TagListResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO na podstawie encji - Tag -> TagList')]
    #[DataProviderExternal(SimpleEntityDataProvider::class, 'tagsValues')]
    public function itCallsDtoFactoryWithCorrectParams(array $tags): void
    {
        // Arrange
        $this->setupFactoryForList(new TagList(), [TagList::class, ...$tags]);

        // Act
        new TagListResponse($this->dtoFactory, ...$tags);
    }
}
