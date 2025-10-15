<?php

namespace App\Dto\Entity\List;

use JsonSerializable;

interface DtoList extends JsonSerializable
{
    public static function getDtoName(): string;
}
