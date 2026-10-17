<?php

namespace App\Dto\List\Type;

use App\Dto\BaseList;

/**
 * @extends BaseList<string>
 */
class StringList extends BaseList
{
    public function __construct(string ...$values)
    {
        $this->entities = $values;
    }
}
