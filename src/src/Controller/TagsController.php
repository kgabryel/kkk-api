<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Factory\Entity\TagFactory;
use App\Repository\TagRepository;
use App\Response\TagListResponse;
use App\Response\TagResponse;
use App\Service\Entity\TagService;
use App\Validation\TagValidation;
use Symfony\Component\HttpFoundation\Response;

class TagsController extends BaseController
{
    public function destroy(int $id, TagService $tagService): Response
    {
        $tag = $tagService->find($id);
        if (!($tag instanceof Tag)) {
            return $this->getNotFoundResponse();
        }

        $tagService->remove($tag);

        return $this->getNoContentResponse();
    }

    public function index(TagRepository $tagRepository): TagListResponse
    {
        return new TagListResponse($this->dtoFactoryDispatcher, ...$tagRepository->findForUser($this->getUser()));
    }

    public function store(TagFactory $tagFactory, TagValidation $tagValidation): Response
    {
        $tag = $tagFactory->create($tagValidation);
        if (!($tag instanceof Tag)) {
            return $this->getBadRequestResponse();
        }

        return new TagResponse($this->dtoFactoryDispatcher, $tag, Response::HTTP_CREATED);
    }

    public function update(int $id, TagValidation $tagValidation, TagService $tagService): Response
    {
        $tag = $tagService->find($id);
        if (!($tag instanceof Tag)) {
            return $this->getNotFoundResponse();
        }

        if (!$tagService->update($tag, $tagValidation)) {
            return $this->getBadRequestResponse();
        }

        return new TagResponse($this->dtoFactoryDispatcher, $tag, Response::HTTP_OK);
    }
}
