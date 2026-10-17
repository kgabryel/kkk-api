<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\OzaSupply;

/**
 * @extends BaseList<OzaSupply>
 */
class OzaSupplyList extends BaseList implements DtoList
{
    public function __construct(OzaSupply ...$supplies)
    {
        $this->entities = $supplies;
    }

    public static function getDtoName(): string
    {
        return OzaSupply::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
