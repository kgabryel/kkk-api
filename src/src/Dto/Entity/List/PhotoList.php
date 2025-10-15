<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\Photo;

/**
 * @extends BaseList<Photo>
 */
class PhotoList extends BaseList implements DtoList
{
    public function __construct(Photo ...$photos)
    {
        $this->entities = $photos;
    }

    public static function getDtoName(): string
    {
        return Photo::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
