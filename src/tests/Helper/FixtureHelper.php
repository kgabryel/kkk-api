<?php

namespace App\Tests\Helper;

class FixtureHelper
{
    public static function getFixture(string $path): string
    {
        return file_get_contents(__DIR__ . '/../resource/' . trim($path));
    }
}
