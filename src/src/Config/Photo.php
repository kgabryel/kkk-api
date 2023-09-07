<?php

namespace App\Config;

abstract class Photo
{
    private const DIMENSIONS = [
        'small' => [
            'width' => 200,
            'height' => 150
        ],
        'medium' => [
            'width' => 800,
            'height' => 600
        ]
    ];
    public const MIN_HEIGHT = 800;
    public const MIN_WIDTH = 600;

    public static function getHeight(PhotoType $type): int
    {
        return self::DIMENSIONS[$type->value]['height'];
    }

    public static function getWidth(PhotoType $type): int
    {
        return self::DIMENSIONS[$type->value]['width'];
    }
}
