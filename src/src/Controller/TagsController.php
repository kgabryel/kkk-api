<?php

namespace App\Controller;

use App\Dto\Tag;
use App\Factory\Entity\TagFactory;
use App\Form\TagForm;
use App\Repository\TagRepository;
use App\Service\Entity\TagService;
use App\Service\SerializeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagsController extends BaseController
{
    private SerializeService $serializer;

    public function __construct()
    {
        $this->serializer = SerializeService::getInstance(Tag::class);
    }

    public function index(TagRepository $tagRepository): Response
    {
        return new Response($this->serializer->serializeArray($tagRepository->findForUser($this->getUser())));
    }

    public function store(TagFactory $tagFactory, Request $request): Response
    {
        $form = $this->createForm(TagForm::class);
        $tag = $tagFactory->create($form, $request);
        if ($tag === null) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($tag));
    }

    public function update(int $id, Request $request, TagService $tagService): Response
    {
        if (!$tagService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $form = $this->createForm(TagForm::class, null, [
            self::EXPECT => $id,
            self::METHOD => Request::METHOD_PUT
        ]);
        if (!$tagService->update($form, $request)) {
            return $this->returnErrors($form);
        }

        return new Response($this->serializer->serialize($tagService->getTag()), Response::HTTP_OK);
    }

    public function destroy(int $id, TagService $tagService): Response
    {
        if (!$tagService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $tagService->remove();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
