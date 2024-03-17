<?php

namespace App\Dto;

interface DtoInterface
{
    public static function createFromEntity(mixed $entity): self;
}
