<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\Settings;
use App\Response\SettingsResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(SettingsResponse::class)]
class SettingsResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - Settings')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $settings = EntityFactory::getSimpleSettings();
        $this->setupFactoryForSingleEntity([Settings::class, $settings]);

        // Act
        new SettingsResponse($this->dtoFactory, $settings, Response::HTTP_OK);
    }
}
