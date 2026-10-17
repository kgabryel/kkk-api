<?php

namespace App\Tests\Unit\Service;

use App\Service\PathService;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\PathDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Small]
#[CoversClass(PathService::class)]
class PathServiceTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca poprawny URL z uwzględnieniem ukośników na końcu i początku')]
    #[DataProviderExternal(PathDataProvider::class, 'frontPathConcatenationValues')]
    public function itReturnsCorrectPath(
        string $frontUrl,
        string $path,
        string $correctResult,
    ): void {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, $this->once()),
            new AllowedMethod('get', $frontUrl, $this->once(), ['FRONT_URL']),
        );
        $pathService = new PathService($parameterBag);

        // Act
        $result = $pathService->getPathToFront($path);

        // Assert
        $this->assertSame($correctResult, $result);
    }

    #[Test]
    #[TestDox('Rzuca wyjątkiem, gdy FRONT_URL jest pusty lub niepoprawny')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidConfigValues')]
    public function itThrowsWhenFrontUrlIsInvalid(mixed $frontUrl): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, $this->once()),
            new AllowedMethod('get', $frontUrl, $this->once(), ['FRONT_URL']),
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FRONT_URL parameter is empty or invalid.');

        // Act
        new PathService($parameterBag);
    }

    #[Test]
    #[TestDox('Rzuca wyjątkiem, gdy FRONT_URL nie jest ustawione')]
    public function itThrowsWhenFrontUrlIsMissing(): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', false, $this->once(), ['FRONT_URL']),
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FRONT_URL parameter is not set.');

        // Act
        new PathService($parameterBag);
    }
}
