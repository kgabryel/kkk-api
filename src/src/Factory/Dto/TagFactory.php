<?php

namespace App\Factory\Dto;

use App\Dto\Entity\Tag;
use App\Entity\Tag as TagEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class TagFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): Tag
    {
        if (!($entity instanceof TagEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', TagEntity::class),
            );
        }

        return new Tag(
            $entity->getId(),
            $entity->getName(),
        );
    }

    public function getDtoName(): string
    {
        return Tag::class;
    }
}
