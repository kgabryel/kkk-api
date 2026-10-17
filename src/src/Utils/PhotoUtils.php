<?php

namespace App\Utils;

use App\Config\PhotoType;
use Imagick;
use ImagickException;

class PhotoUtils
{
    /**
     * @throws ImagickException
     */
    public static function fromBlob(string $data): Imagick
    {
        $image = new Imagick();
        $image->readImageBlob($data);

        return $image;
    }

    public static function getFilesDirectory(string $baseDirectory): string
    {
        return sprintf('%s%svar%sfiles', $baseDirectory, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
    }

    public static function getPath(string $baseDirectory, PhotoType $type, string $fileName): string
    {
        return sprintf(
            '%s%s%s',
            self::getTypeDirectory($baseDirectory, $type),
            DIRECTORY_SEPARATOR,
            $fileName,
        );
    }

    public static function getTypeDirectory(string $baseDirectory, PhotoType $type): string
    {
        return sprintf(
            '%s%s%s',
            self::getFilesDirectory($baseDirectory),
            DIRECTORY_SEPARATOR,
            $type->value,
        );
    }
}
