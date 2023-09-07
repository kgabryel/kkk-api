<?php

namespace App\Factory\Entity;

use App\Entity\Season;
use App\Model\Season as SeasonModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SeasonFactory extends EntityFactory
{
    public function create(FormInterface $form, Request $request): ?Season
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }
        /** @var SeasonModel $data */
        $data = $form->getData();
        $season = new Season();
        $season->setUser($this->user);
        $season->setIngredient($data->getIngredient());
        $season->setStart($data->getStart());
        $season->setStop($data->getStop());
        $this->saveEntity($season);

        return $season;
    }
}
