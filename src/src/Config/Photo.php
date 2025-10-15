<?php

namespace App\Config;

use InvalidArgumentException;

abstract class Photo
{
    public const int MIN_HEIGHT = 600;
    public const int MIN_WIDTH = 800;
    private const array DIMENSIONS = [
        PhotoType::MEDIUM->value => [
            self::HEIGHT => 600,
            self::WIDTH => 800,
        ],
        PhotoType::SMALL->value => [
            self::HEIGHT => 150,
            self::WIDTH => 200,
        ],
    ];
    private const string HEIGHT = 'height';
    private const string WIDTH = 'width';

    public static function getHeight(PhotoType $type): int
    {
        if ($type === PhotoType::ORIGINAL) {
            throw new InvalidArgumentException(sprintf('Unsupported photo type: %s', $type->value));
        }

        return self::DIMENSIONS[$type->value][self::HEIGHT];
    }

    public static function getWidth(PhotoType $type): int
    {
        if ($type === PhotoType::ORIGINAL) {
            throw new InvalidArgumentException(sprintf('Unsupported photo type: %s', $type->value));
        }

        return self::DIMENSIONS[$type->value][self::WIDTH];
    }
}
