<?php

namespace App\Factory\Entity;

use App\Config\PhotoType;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Repository\PhotoRepository;
use App\Service\Photo\PhotoDimensionValidator;
use App\Service\Photo\PhotoScaler;
use App\Service\Photo\PhotoStorage;
use App\Service\UserService;
use App\Utils\PhotoUtils;
use App\Utils\UuidGenerator;
use App\Validation\PhotoValidation;
use Doctrine\ORM\EntityManagerInterface;
use Imagick;
use ImagickException;

class PhotoFactory extends EntityFactory
{
    private PhotoDimensionValidator $dimensionValidator;
    private string $fileName;
    private Imagick $image;
    private PhotoRepository $photoRepository;
    private PhotoScaler $photoScaler;
    private PhotoStorage $photoStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        PhotoStorage $photoStorage,
        PhotoRepository $photoRepository,
        PhotoScaler $photoScaler,
        PhotoDimensionValidator $dimensionValidator,
        UuidGenerator $uuidGenerator,
    ) {
        parent::__construct($entityManager, $userService);
        $this->fileName = $uuidGenerator->generate();
        $this->photoStorage = $photoStorage;
        $this->photoRepository = $photoRepository;
        $this->photoScaler = $photoScaler;
        $this->dimensionValidator = $dimensionValidator;
    }

    public function create(PhotoValidation $photoValidation, Recipe $recipe): false|Photo
    {
        if (!$photoValidation->validate()->passed()) {
            return false;
        }

        try {
            $this->image = PhotoUtils::fromBlob($photoValidation->getDto()->getDecoded());
            if (!$this->dimensionValidator->isValid($this->image)) {
                return false;
            }

            $this->photoStorage->saveFile(PhotoType::ORIGINAL, $this->image, $this->fileName);
            $sizes = [PhotoType::MEDIUM, PhotoType::SMALL];
            foreach ($sizes as $size) {
                $this->photoStorage->saveFile($size, $this->photoScaler->scale($this->image, $size), $this->fileName);
            }
        } catch (ImagickException) {
            return false;
        }

        return $this->createEntity($recipe);
    }

    private function createEntity(Recipe $recipe): Photo
    {
        $order = $this->photoRepository->getNextPhotoOrderForRecipe($recipe);
        $photo = new Photo();
        $photo->setUser($this->user);
        $photo->setFileName($this->fileName);
        $photo->setHeight($this->image->getImageHeight());
        $photo->setWidth($this->image->getImageWidth());
        $photo->setType($this->image->getImageMimeType());
        $photo->setPhotoOrder($order);
        $recipe->addPhoto($photo);
        $this->saveEntity($photo);

        return $photo;
    }
}
