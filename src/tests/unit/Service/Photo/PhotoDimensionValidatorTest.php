<?php

namespace App\Tests\Unit\Service\Photo;

use App\Config\Photo;
use App\Service\Photo\PhotoDimensionValidator;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedExceptionMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use Imagick;
use ImagickException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(PhotoDimensionValidator::class)]
class PhotoDimensionValidatorTest extends BaseTestCase
{
    private PhotoDimensionValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PhotoDimensionValidator();
    }

    #[Test]
    #[TestDox('Akceptuje obraz o poprawnych proporcjach')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validPhotoSizesValues')]
    public function itAcceptsValidProportions(int $height, int $width): void
    {
        // Arrange
        $imagick = $this->getMock(
            Imagick::class,
            new AllowedMethod('getImageHeight', $height, $this->atLeastOnce()),
            new AllowedMethod('getImageWidth', $width, $this->atLeastOnce()),
        );

        // Act
        $result = $this->validator->isValid($imagick);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('Odrzuca obraz o niepoprawnych proporcjach')]
    public function itRejectsInvalidProportions(): void
    {
        // Arrange
        $height = 600;
        $width = (int)($height * 4 / 3) + 11;
        $imagick = $this->getMock(
            Imagick::class,
            new AllowedMethod('getImageHeight', $height, $this->atLeastOnce()),
            new AllowedMethod('getImageWidth', $width, $this->atLeastOnce()),
        );

        // Act
        $result = $this->validator->isValid($imagick);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Odrzuca obraz, gdy wysokość jest zbyt mała')]
    public function itRejectsWhenHeightTooSmall(): void
    {
        // Arrange
        $imagick = $this->getMock(
            Imagick::class,
            new AllowedMethod('getImageHeight', Photo::MIN_HEIGHT - 1, $this->once()),
            new AllowedMethod('getImageWidth', Photo::MIN_WIDTH + 10),
        );

        // Act
        $result = $this->validator->isValid($imagick);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Odrzuca obraz, gdy szerokość jest zbyt mała')]
    public function itRejectsWhenWidthTooSmall(): void
    {
        // Arrange
        $imagick = $this->getMock(
            Imagick::class,
            new AllowedMethod('getImageHeight', Photo::MIN_HEIGHT + 10),
            new AllowedMethod('getImageWidth', Photo::MIN_WIDTH - 1, $this->once()),
        );

        // Act
        $result = $this->validator->isValid($imagick);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Odrzuca obraz, gdy podczas sprawdzania wystąpi wyjątek')]
    public function itReturnsFalseWhenExceptionIsThrown(): void
    {
        // Arrange
        $imagick = $this->getMock(
            Imagick::class,
            new AllowedExceptionMethod('getImageHeight', $this->once(), new ImagickException()),
            new AllowedMethod('getImageWidth', overrideValue: false),
        );

        // Act
        $result = $this->validator->isValid($imagick);

        // Assert
        $this->assertFalse($result);
    }
}
