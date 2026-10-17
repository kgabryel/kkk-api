<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\Tag;
use App\Response\TagResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(TagResponse::class)]
class TagResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - Tag')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $tag = EntityFactory::getSimpleTag();
        $this->setupFactoryForSingleEntity([Tag::class, $tag]);

        // Act
        new TagResponse($this->dtoFactory, $tag, Response::HTTP_OK);
    }
}
