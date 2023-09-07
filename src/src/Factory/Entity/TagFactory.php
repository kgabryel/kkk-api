<?php

namespace App\Factory\Entity;

use App\Entity\Tag;
use App\Model\Tag as TagModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TagFactory extends EntityFactory
{
    public function create(FormInterface $form, Request $request): ?Tag
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }
        /** @var TagModel $data */
        $data = $form->getData();
        $tag = new Tag();
        $tag->setUser($this->user);
        $tag->setName($data->getName());
        $this->saveEntity($tag);

        return $tag;
    }
}
