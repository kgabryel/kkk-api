<?php

namespace App\Utils;

class PhotoUtils
{
    public static function getPath(string $baseDirectory, string $type, string $fileName): string
    {
        return sprintf(
            '%s%s%s',
            self::getTypeDirectory($baseDirectory, $type),
            DIRECTORY_SEPARATOR,
            $fileName
        );
    }

    public static function getTypeDirectory(string $baseDirectory, string $type): string
    {
        return sprintf(
            '%s%s%s',
            self::getFilesDirectory($baseDirectory),
            DIRECTORY_SEPARATOR,
            $type
        );
    }

    public static function getFilesDirectory(string $baseDirectory): string
    {
        return sprintf(
            '%s%svar%sfiles',
            $baseDirectory,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
    }
}
