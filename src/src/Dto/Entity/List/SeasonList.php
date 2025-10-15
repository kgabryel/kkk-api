<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\Season;

/**
 * @extends BaseList<Season>
 */
class SeasonList extends BaseList implements DtoList
{
    public function __construct(Season ...$seasons)
    {
        $this->entities = $seasons;
    }

    public static function getDtoName(): string
    {
        return Season::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
