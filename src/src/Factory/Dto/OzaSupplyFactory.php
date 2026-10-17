<?php

namespace App\Factory\Dto;

use App\Dto\Entity\OzaSupply;
use App\Factory\DtoFactoryDispatcher;

class OzaSupplyFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): OzaSupply
    {
        $id = (int)($entity->id ?? 0);
        $name = (string)($entity->group->name ?? '');
        $shortName = (string)($entity->unit->shortcut ?? '');
        $amount = (string)($entity->amount ?? '');

        return new OzaSupply($id, $name, $amount > 0, sprintf('%s%s', $amount, $shortName));
    }

    public function getDtoName(): string
    {
        return OzaSupply::class;
    }
}
