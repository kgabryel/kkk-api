<?php

namespace App\Factory\Dto;

use App\Dto\Entity\Settings;
use App\Entity\Settings as SettingsEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class SettingsFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): Settings
    {
        if (!($entity instanceof SettingsEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', SettingsEntity::class),
            );
        }

        return new Settings(
            $entity->getAutocomplete(),
            $entity->getOzaKey(),
            $entity->getUser()->getFbId() === null,
        );
    }

    public function getDtoName(): string
    {
        return Settings::class;
    }
}
