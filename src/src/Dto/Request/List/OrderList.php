<?php

namespace App\Dto\Request\List;

use App\Dto\BaseList;
use App\Dto\Request\Order;

/**
 * @extends BaseList<Order>
 */
class OrderList extends BaseList
{
    public function __construct(Order ...$orders)
    {
        $this->entities = $orders;
    }
}
