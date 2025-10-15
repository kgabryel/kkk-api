<?php

namespace App\Controller;

use App\Config\PhotoType;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Factory\Entity\PhotoFactory;
use App\Repository\PhotoRepository;
use App\Response\RecipeResponse;
use App\Service\Entity\PhotoService;
use App\Service\Entity\RecipeService;
use App\Utils\PhotoUtils;
use App\Validation\PhotoValidation;
use App\Validation\ReorderPhotosValidation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class PhotosController extends BaseController
{
    public function destroy(int $photoId, PhotoService $photoService): Response
    {
        $photo = $photoService->find($photoId);
        if (!($photo instanceof Photo)) {
            return $this->getNotFoundResponse();
        }

        $recipe = $photo->getRecipe();
        $photoService->remove($photo);

        return new RecipeResponse($this->dtoFactoryDispatcher, $recipe, Response::HTTP_OK);
    }

    public function reorderPhotos(
        int $id,
        RecipeService $recipeService,
        ReorderPhotosValidation $photosValidation,
    ): Response {
        $recipe = $recipeService->find($id);
        if (!($recipe instanceof Recipe)) {
            return $this->getNotFoundResponse();
        }

        if (!$recipeService->reorderPhotos($recipe, $photosValidation)) {
            return $this->getBadRequestResponse();
        }

        return new RecipeResponse($this->dtoFactoryDispatcher, $recipe, Response::HTTP_OK);
    }

    public function show(
        string $type,
        int $photoId,
        PhotoRepository $photoRepository,
        KernelInterface $kernel,
        PhotoService $photoService,
    ): Response {
        $photo = $photoRepository->find($photoId);
        if ($photo === null) {
            return $this->getForbiddenResponse();
        }

        if (!$photoService->checkAccess($photo, $this->getUser())) {
            return $this->getForbiddenResponse();
        }

        $response = new Response();
        $response->headers->set('Content-Type', $photo->getType());
        $fileName = $photo->getFileName() ?? '';
        $response->setContent(
            file_get_contents(PhotoUtils::getPath($kernel->getProjectDir(), PhotoType::from($type), $fileName)),
        );

        return $response;
    }

    public function store(
        int $id,
        RecipeService $recipeService,
        PhotoFactory $photoFactory,
        PhotoValidation $photoValidation,
    ): Response {
        $recipe = $recipeService->find($id);
        if (!($recipe instanceof Recipe)) {
            return $this->getNotFoundResponse();
        }

        $photo = $photoFactory->create($photoValidation, $recipe);
        if ($photo === false) {
            return $this->getBadRequestResponse();
        }

        return new RecipeResponse($this->dtoFactoryDispatcher, $recipe, Response::HTTP_CREATED);
    }
}
