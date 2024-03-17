<?php

namespace App\Controller;

use App\Dto\Recipe;
use App\Entity\Photo;
use App\Factory\Entity\PhotoFactory;
use App\Form\PhotoForm;
use App\Repository\PhotoRepository;
use App\Service\Entity\PhotoService;
use App\Service\Entity\RecipeService;
use App\Service\SerializeService;
use App\Utils\PhotoUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class PhotosController extends BaseController
{
    private SerializeService $serializer;

    public function __construct()
    {
        $this->serializer = SerializeService::getInstance(Recipe::class);
    }

    public function show(
        string $type,
        int $photoId,
        PhotoRepository $photoRepository,
        KernelInterface $kernel
    ): Response {
        $photo = $photoRepository->find($photoId);
        if ($photo === null) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }
        if (!PhotoService::checkAccess($photo, $this->getUser())) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }
        $response = new Response();
        $response->headers->set('Content-Type', $photo->getType());
        $fileName = $photo->getFileName() ?? '';
        $response->setContent(
            (string)file_get_contents(PhotoUtils::getPath($kernel->getProjectDir(), $type, $fileName))
        );

        return $response;
    }

    public function store(int $id, RecipeService $recipeService, PhotoFactory $photoFactory, Request $request): Response
    {
        if (!$recipeService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(PhotoForm::class, null, [
            'method' => Request::METHOD_POST
        ]);
        $photo = $photoFactory->create($form, $request, $recipeService->getRecipe());
        if ($photo === false) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        return new Response($this->serializer->serialize($recipeService->getRecipe()), Response::HTTP_OK);
    }

    public function destroy(int $photoId, PhotoService $photoService): Response
    {
        if (!$photoService->find($photoId)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        /** @var Photo $recipe */
        $recipe = $photoService->getPhoto()->getRecipe();
        $photoService->remove();

        return new Response($this->serializer->serialize($recipe), Response::HTTP_OK);
    }

    public function reorderPhotos(int $id, RecipeService $recipeService, Request $request): Response
    {
        if (!$recipeService->find($id)) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        $recipeService->reorderPhotos($request);

        return new Response($this->serializer->serialize($recipeService->getRecipe()), Response::HTTP_OK);
    }
}
