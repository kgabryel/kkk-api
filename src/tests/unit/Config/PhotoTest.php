<?php

namespace App\Tests\Unit\Config;

use App\Config\Photo;
use App\Config\PhotoType;
use App\Tests\Helper\TestCase\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(Photo::class)]
class PhotoTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Rzuca wyjątkiem przy próbie pobrania wysokości dla typu ORIGINAL')]
    public function itRejectsOriginalHeightAccess(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported photo type: original');

        // Act
        Photo::getHeight(PhotoType::ORIGINAL);
    }

    #[Test]
    #[TestDox('Rzuca wyjątkiem przy próbie pobrania szerokości dla typu ORIGINAL')]
    public function itRejectsOriginalWidthAccess(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported photo type: original');

        // Act
        Photo::getWidth(PhotoType::ORIGINAL);
    }

    #[Test]
    #[TestDox('Zwraca wysokość dla średniego zdjęcia')]
    public function itReturnsHeightForMediumPhoto(): void
    {
        // Act
        $height = Photo::getHeight(PhotoType::MEDIUM);

        // Assert
        $this->assertSame(600, $height);
    }

    #[Test]
    #[TestDox('Zwraca wysokość dla małego zdjęcia')]
    public function itReturnsHeightForSmallPhoto(): void
    {
        // Act
        $height = Photo::getHeight(PhotoType::SMALL);

        // Assert
        $this->assertSame(150, $height);
    }

    #[Test]
    #[TestDox('Zwraca szerokość dla średniego zdjęcia')]
    public function itReturnsWidthForMediumPhoto(): void
    {
        // Act
        $width = Photo::getWidth(PhotoType::MEDIUM);

        // Assert
        $this->assertSame(800, $width);
    }

    #[Test]
    #[TestDox('Zwraca szerokość dla małego zdjęcia')]
    public function itReturnsWidthForSmallPhoto(): void
    {
        // Act
        $width = Photo::getWidth(PhotoType::SMALL);

        // Assert
        $this->assertSame(200, $width);
    }
}
