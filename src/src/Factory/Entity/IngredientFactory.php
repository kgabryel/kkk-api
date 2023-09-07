<?php

namespace App\Factory\Entity;

use App\Entity\Ingredient;
use App\Model\Ingredient as IngredientModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class IngredientFactory extends EntityFactory
{
    public function create(FormInterface $form, Request $request): ?Ingredient
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }
        /** @var IngredientModel $data */
        $data = $form->getData();
        $ingredient = new Ingredient();
        $ingredient->setUser($this->user);
        $ingredient->setName($data->getName());
        $ingredient->setAvailable($data->isAvailable());
        $ingredient->setOzaId($data->getOzaId());
        $this->saveEntity($ingredient);

        return $ingredient;
    }
}
