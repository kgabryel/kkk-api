<?php

namespace App\Dto\List\Entity;

use App\Dto\BaseList;
use App\Entity\Photo;

/**
 * @extends BaseList<Photo>
 */
class PhotoList extends BaseList
{
    public function __construct(Photo ...$photos)
    {
        $this->entities = $photos;
    }
}
