<?php

namespace App\Factory\Entity;

use App\Entity\Tag;
use App\Validation\TagValidation;

class TagFactory extends EntityFactory
{
    public function create(TagValidation $tagValidation): ?Tag
    {
        if (!$tagValidation->validate()->passed()) {
            return null;
        }

        $data = $tagValidation->getDto();
        $tag = new Tag();
        $tag->setUser($this->user);
        $tag->setName($data->getName());
        $this->saveEntity($tag);

        return $tag;
    }
}
