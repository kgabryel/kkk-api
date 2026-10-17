<?php

namespace App\Factory\Dto;

use App\Dto\Entity\DtoInterface;
use App\Factory\DtoFactoryDispatcher;

interface DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): DtoInterface;

    public function getDtoName(): string;
}
