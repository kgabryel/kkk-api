<?php

namespace App\Factory\Dto;

use App\Dto\Entity\Photo;
use App\Entity\Photo as PhotoEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class PhotoFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): Photo
    {
        if (!($entity instanceof PhotoEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', PhotoEntity::class),
            );
        }

        return new Photo(
            $entity->getId(),
            $entity->getWidth(),
            $entity->getHeight(),
            $entity->getType(),
        );
    }

    public function getDtoName(): string
    {
        return Photo::class;
    }
}
