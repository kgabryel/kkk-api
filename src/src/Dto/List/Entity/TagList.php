<?php

namespace App\Dto\List\Entity;

use App\Dto\BaseList;
use App\Entity\Tag;

/**
 * @extends BaseList<Tag>
 */
class TagList extends BaseList
{
    public function __construct(Tag ...$tags)
    {
        $this->entities = $tags;
    }
}
