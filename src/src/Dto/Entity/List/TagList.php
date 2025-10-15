<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\Tag;

/**
 * @extends BaseList<Tag>
 */
class TagList extends BaseList implements DtoList
{
    public function __construct(Tag ...$tags)
    {
        $this->entities = $tags;
    }

    public static function getDtoName(): string
    {
        return Tag::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
