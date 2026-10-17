<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\Season;
use App\Response\SeasonResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(SeasonResponse::class)]
class SeasonResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - Season')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $season = EntityFactory::getSimpleSeason();
        $this->setupFactoryForSingleEntity([Season::class, $season]);

        // Act
        new SeasonResponse($this->dtoFactory, $season, Response::HTTP_OK);
    }
}
