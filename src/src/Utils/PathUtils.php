<?php

namespace App\Utils;

class PathUtils
{
    public static function getPathToFront(string $path): string
    {
        return sprintf('%s%s', $_ENV['FRONT_URL'], trim($path, '/'));
    }
}
