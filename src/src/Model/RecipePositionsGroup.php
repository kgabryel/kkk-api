<?php

namespace App\Model;

class RecipePositionsGroup
{
    private ?string $name;
    /** @var RecipePosition[] */
    private array $positions;

    public function __construct()
    {
        $this->name = null;
        $this->positions = [];
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return RecipePosition[]
     */
    public function getPositions(): array
    {
        return $this->positions;
    }

    /**
     * @param  RecipePosition[]  $positions
     */
    public function setPositions(array $positions): void
    {
        $this->positions = $positions;
    }
}
