<?php

namespace App\Dto;

/**
 * @template T
 */
abstract class BaseList
{
    /**
     * @var T[]
     */
    protected array $entities;

    /**
     * @return T[]
     */
    public function get(): array
    {
        return $this->entities;
    }
}
