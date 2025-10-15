<?php

namespace App\Tests\Unit\Utils;

use App\Config\PhotoType;
use App\Tests\DataProvider\PathDataProvider;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Utils\PhotoUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(PhotoUtils::class)]
class PhotoUtilsTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca ścieżkę do katalogu var/files dla podanej ścieżki bazowej')]
    #[DataProviderExternal(PathDataProvider::class, 'filesDirectoryValues')]
    public function itReturnsFilesDirectoryPath(string $baseDir, string $expected): void
    {
        // Act
        $path = PhotoUtils::getFilesDirectory($baseDir);

        // Assert
        $this->assertSame($expected, $path);
    }

    #[Test]
    #[TestDox('Zwraca pełną ścieżkę do pliku na podstawie katalogu, typu i nazwy pliku')]
    #[DataProviderExternal(PathDataProvider::class, 'fullFilePathValues')]
    public function itReturnsFullFilePath(string $baseDir, PhotoType $type, string $fileName, string $expected): void
    {
        // Act
        $path = PhotoUtils::getPath($baseDir, $type, $fileName);

        // Assert
        $this->assertSame($expected, $path);
    }

    #[Test]
    #[TestDox('Zwraca ścieżkę katalogu typu (np. small/medium) wewnątrz var/files')]
    #[DataProviderExternal(PathDataProvider::class, 'photoTypeDirectoryValues')]
    public function itReturnsTypeDirectoryPath(string $baseDir, PhotoType $type, string $expected): void
    {
        // Act
        $path = PhotoUtils::getTypeDirectory($baseDir, $type);

        // Assert
        $this->assertSame($expected, $path);
    }
}
