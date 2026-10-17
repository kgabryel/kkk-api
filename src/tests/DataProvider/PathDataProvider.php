<?php

namespace App\Tests\DataProvider;

use App\Config\PhotoType;

class PathDataProvider
{
    public static function filesDirectoryValues(): array
    {
        return [
            'ścieżka tymczasowa (/tmp)' => [
                'baseDir' => '/tmp',
                'expected' => '/tmp' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'files',
            ],
            'ścieżka w katalogu projektu (/var/www/html/api)' => [
                'baseDir' => '/var/www/html/api',
                'expected' => '/var/www/html/api' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'files',
            ],
        ];
    }

    public static function frontPathConcatenationValues(): array
    {
        return [
            'bazowy URL bez ukośnika + ścieżka bez ukośnika' => [
                'correctResult' => 'https://example.com/test',
                'frontUrl' => 'https://example.com',
                'path' => 'test',
            ],
            'bazowy URL bez ukośnika + ścieżka z ukośnikiem na końcu' => [
                'correctResult' => 'https://example.com/test/path',
                'frontUrl' => 'https://example.com',
                'path' => 'test/path/',
            ],
            'bazowy URL bez ukośnika + ścieżka z ukośnikiem na początku' => [
                'correctResult' => 'https://example.com/test',
                'frontUrl' => 'https://example.com',
                'path' => '/test',
            ],
            'bazowy URL błędnie zaczynający się od "/"' => [
                'correctResult' => 'https://example.com/test',
                'frontUrl' => '/https://example.com/',
                'path' => 'test/',
            ],
            'bazowy URL z ukośnikiem + ścieżka bez ukośnika' => [
                'correctResult' => 'https://example.com/test/path',
                'frontUrl' => 'https://example.com/',
                'path' => 'test/path',
            ],
            'bazowy URL z ukośnikiem + ścieżka z ukośnikiem' => [
                'correctResult' => 'https://example.pl/test/path',
                'frontUrl' => 'https://example.pl/',
                'path' => '/test/path',
            ],
            'oba mają ukośniki -> wynik bez podwójnego' => [
                'correctResult' => 'https://front.url/test',
                'frontUrl' => 'https://front.url/',
                'path' => '/test/',
            ],
            'pusta ścieżka' => [
                'correctResult' => 'https://example.com',
                'frontUrl' => 'https://example.com/',
                'path' => '',
            ],
        ];
    }

    public static function fullFilePathValues(): array
    {
        return [
            'ścieżka w katalogu projektu + photo type SMALL' => [
                'baseDir' => '/var/www/html/api',
                'expected' => '/var/www/html/api' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'small' . DIRECTORY_SEPARATOR . 'img123.jpg',
                'fileName' => 'img123.jpg',
                'type' => PhotoType::SMALL,
            ],
            'ścieżka w katalogu tymczasowym + photo type MEDIUM' => [
                'baseDir' => '/tmp',
                'expected' => '/tmp' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'medium' . DIRECTORY_SEPARATOR . 'image.png',
                'fileName' => 'image.png',
                'type' => PhotoType::MEDIUM,
            ],
        ];
    }

    public static function photoTypeDirectoryValues(): array
    {
        return [
            'ścieżka w katalogu projektu + typ SMALL' => [
                'baseDir' => '/var/www/html/api',
                'expected' => '/var/www/html/api' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'small',
                'type' => PhotoType::SMALL,
            ],
            'ścieżka w katalogu tymczasowym + typ MEDIUM' => [
                'baseDir' => '/tmp',
                'expected' => '/tmp' . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'medium',
                'type' => PhotoType::MEDIUM,
            ],
        ];
    }
}
