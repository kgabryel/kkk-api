<?php

namespace App\Service\Photo;

use App\Config\Photo;
use App\Config\PhotoType;
use Imagick;

class PhotoScaler
{
    public function scale(Imagick $image, PhotoType $type): Imagick
    {
        $clone = clone $image;
        $clone->scaleImage(Photo::getWidth($type), Photo::getHeight($type));

        return $clone;
    }
}
