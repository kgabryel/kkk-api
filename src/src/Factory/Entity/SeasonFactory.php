<?php

namespace App\Factory\Entity;

use App\Entity\Season;
use App\Validation\SeasonValidation;

class SeasonFactory extends EntityFactory
{
    public function create(SeasonValidation $seasonValidation): ?Season
    {
        if (!$seasonValidation->validate()->passed()) {
            return null;
        }

        $data = $seasonValidation->getDto();
        $season = new Season();
        $season->setUser($this->user);
        $season->setIngredient($data->getIngredient());
        $season->setStart($data->getStart());
        $season->setStop($data->getStop());
        $this->saveEntity($season);

        return $season;
    }
}
