<?php

namespace App\Dto\List\Type;

use App\Dto\BaseList;

/**
 * @extends BaseList<int>
 */
class IntList extends BaseList
{
    public function __construct(int ...$values)
    {
        $this->entities = $values;
    }
}
