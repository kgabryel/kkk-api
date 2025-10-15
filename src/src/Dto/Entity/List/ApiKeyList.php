<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\ApiKey;

/**
 * @extends BaseList<ApiKey>
 */
class ApiKeyList extends BaseList implements DtoList
{
    public function __construct(ApiKey ...$keys)
    {
        $this->entities = $keys;
    }

    public static function getDtoName(): string
    {
        return ApiKey::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
