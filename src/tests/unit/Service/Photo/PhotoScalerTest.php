<?php

namespace App\Tests\Unit\Service\Photo;

use App\Config\PhotoType;
use App\Service\Photo\PhotoScaler;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use Imagick;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(PhotoScaler::class)]
class PhotoScalerTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca przeskalowaną kopię zdjęcia w rozmiarze 800x600')]
    public function itScalesImageClone(): void
    {
        $imageMock = $this->getMock(
            Imagick::class,
            new AllowedMethod(
                'scaleImage',
                invokedCount: $this->once(),
                parameters: [800, 600],
                overrideValue: false,
            ),
        );
        $scaler = new PhotoScaler();

        // Act
        $scaledImage = $scaler->scale($imageMock, PhotoType::MEDIUM);

        // Assert
        $this->assertNotSame($imageMock, $scaledImage);
    }
}
