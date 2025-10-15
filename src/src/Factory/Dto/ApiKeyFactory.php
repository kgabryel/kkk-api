<?php

namespace App\Factory\Dto;

use App\Dto\Entity\ApiKey;
use App\Entity\ApiKey as ApiKeyEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class ApiKeyFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): ApiKey
    {
        if (!($entity instanceof ApiKeyEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', ApiKeyEntity::class),
            );
        }

        return new ApiKey(
            $entity->getId(),
            $entity->getKey(),
            $entity->isActive(),
        );
    }

    public function getDtoName(): string
    {
        return ApiKey::class;
    }
}
