<?php

namespace App\Dto;

interface DtoInterface
{
    public static function createFromEntity($entity): self;
}
