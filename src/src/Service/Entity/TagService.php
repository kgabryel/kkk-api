<?php

namespace App\Service\Entity;

use App\Entity\Tag;
use App\Model\Tag as TagModel;
use App\Repository\TagRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TagService extends EntityService
{
    private Tag $tag;
    private TagRepository $tagRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        TagRepository $tagRepository
    ) {
        parent::__construct($entityManager, $userService);
        $this->tagRepository = $tagRepository;
    }

    public function find(int $id): bool
    {
        $tag = $this->tagRepository->findById($id, $this->user);
        if ($tag === null) {
            return false;
        }
        $this->tag = $tag;

        return true;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function update(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        /** @var TagModel $data */
        $data = $form->getData();
        $this->tag->setName($data->getName());
        $this->saveEntity($this->tag);

        return true;
    }

    public function remove(): void
    {
        $this->removeEntity($this->tag);
    }
}
