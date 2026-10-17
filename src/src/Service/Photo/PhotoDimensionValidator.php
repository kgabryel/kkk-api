<?php

namespace App\Service\Photo;

use App\Config\Photo;
use Imagick;
use ImagickException;

class PhotoDimensionValidator
{
    public function isValid(Imagick $image): bool
    {
        try {
            if ($image->getImageHeight() < Photo::MIN_HEIGHT) {
                return false;
            }

            if ($image->getImageWidth() < Photo::MIN_WIDTH) {
                return false;
            }

            $width = $image->getImageWidth();
            $height = $image->getImageHeight() * (4 / 3);
        } catch (ImagickException) {
            return false;
        }

        return abs($width - $height) <= 10;
    }
}
