<?php

namespace App\Service\Entity;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\UserService;
use App\Validation\TagValidation;
use Doctrine\ORM\EntityManagerInterface;

class TagService extends EntityService
{
    private TagRepository $tagRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        TagRepository $tagRepository,
    ) {
        parent::__construct($entityManager, $userService);
        $this->tagRepository = $tagRepository;
    }

    public function find(int $id): ?Tag
    {
        return $this->tagRepository->findById($id, $this->user);
    }

    public function remove(Tag $tag): void
    {
        $this->removeEntity($tag);
    }

    public function update(Tag $tag, TagValidation $tagValidation): bool
    {
        $tagValidation->setExpect($tag->getId());
        if (!$tagValidation->validate()->passed()) {
            return false;
        }

        $data = $tagValidation->getDto();
        $tag->setName($data->getName());
        $this->saveEntity($tag);

        return true;
    }
}
