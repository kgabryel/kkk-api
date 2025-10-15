<?php

namespace App\Tests\Integration\Service\Photo;

use App\Config\PhotoType;
use App\Service\Photo\PhotoStorage;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Utils\PhotoUtils;
use Imagick;
use ImagickPixel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

#[Medium]
#[CoversClass(PhotoStorage::class)]
class PhotoStorageTest extends BaseIntegrationTestCase
{
    #[Test]
    #[TestDox('Zapisuje obraz na dysku i tworzy odpowiednie katalogi')]
    public function itSavesFileOnDisk(): void
    {
        // Arrange
        $dir = sys_get_temp_dir() . '/photo-test';
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($dir);
        $filesystem = new Filesystem();
        $imagick = new Imagick();
        $imagick->newImage(10, 10, new ImagickPixel('white'));
        $imagick->setImageFormat('png');
        $service = new PhotoStorage($filesystem, $kernel);
        $filePath = PhotoUtils::getPath($dir, PhotoType::ORIGINAL, 'test.png');

        // Act
        $service->saveFile(PhotoType::ORIGINAL, $imagick, 'test.png');

        // Assert
        $this->assertFileExists($filePath);
        $this->assertGreaterThan(0, filesize($filePath));

        // Clean
        $filesystem->remove($dir);
    }
}
