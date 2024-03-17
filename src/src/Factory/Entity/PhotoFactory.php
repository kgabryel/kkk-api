<?php

namespace App\Factory\Entity;

use App\Config\Photo as PhotoConfig;
use App\Config\PhotoType;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Model\Photo as PhotoModel;
use App\Service\UserService;
use App\Utils\PhotoUtils;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Imagick;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class PhotoFactory extends EntityFactory
{
    private string $fileName;
    private Filesystem $filesystem;
    private Imagick $image;
    private KernelInterface $kernel;
    private Imagick $originalImage;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        KernelInterface $kernel
    ) {
        parent::__construct($entityManager, $userService);
        $this->filesystem = new Filesystem();
        $this->fileName = Uuid::uuid4()->toString();
        $this->image = new Imagick();
        $this->originalImage = new Imagick();
        $this->kernel = $kernel;
    }

    public function create(FormInterface $form, Request $request, Recipe $recipe): false|Photo
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        /** @var PhotoModel $data */
        $data = $form->getData();
        $base64 = $data->getPhoto() ?? '';
        $position = strpos($base64, 'base64,');
        if ($position === false) {
            return false;
        }
        $base64 = substr($base64, $position + strlen('base64,'));
        try {
            $this->originalImage->readImageBlob(base64_decode($base64));
            if (!$this->validatePhoto()) {
                return false;
            }
            $this->image = $this->originalImage->clone();
            $this->saveFile(PhotoType::ORIGINAL);
            $this->image->scaleImage(
                PhotoConfig::getWidth(PhotoType::MEDIUM),
                PhotoConfig::getHeight(PhotoType::MEDIUM)
            );
            $this->saveFile(PhotoType::MEDIUM);
            $this->image = $this->originalImage->clone();
            $this->image->scaleImage(
                PhotoConfig::getWidth(PhotoType::SMALL),
                PhotoConfig::getHeight(PhotoType::SMALL)
            );
            $this->saveFile(PhotoType::SMALL);
        } catch (Exception $exception) {
            return false;
        }
        $photo = new Photo();
        $photo->setUser($this->user);
        $photo->setFileName($this->fileName);
        $photo->setHeight($this->originalImage->getImageHeight());
        $photo->setWidth($this->originalImage->getImageWidth());
        $photo->setType($this->originalImage->getImageMimeType());
        $photos = $recipe->getPhotos()->toArray();
        $order = 1;
        if ($photos !== []) {
            $order = ($photos[array_key_last($photos)]->getPhotoOrder() ?? 0) + 1;
        }
        $photo->setPhotoOrder($order);
        $recipe->addPhoto($photo);
        $this->saveEntity($photo);

        return $photo;
    }

    private function validatePhoto(): bool
    {
        if ($this->originalImage->getImageHeight() < PhotoConfig::MIN_HEIGHT) {
            return false;
        }
        if ($this->originalImage->getImageWidth() < PhotoConfig::MIN_WIDTH) {
            return false;
        }
        $width = $this->originalImage->getImageWidth();
        $height = $this->originalImage->getImageHeight() * (4 / 3);

        return abs($width - $height) <= 10;
    }

    private function saveFile(PhotoType $type): void
    {
        if (!$this->filesystem->exists(PhotoUtils::getFilesDirectory($this->kernel->getProjectDir()))) {
            $this->filesystem->mkdir(PhotoUtils::getFilesDirectory($this->kernel->getProjectDir()));
        }
        if (!$this->filesystem->exists(PhotoUtils::getTypeDirectory($this->kernel->getProjectDir(), $type->value))) {
            $this->filesystem->mkdir(PhotoUtils::getTypeDirectory($this->kernel->getProjectDir(), $type->value));
        }
        $this->filesystem->touch(PhotoUtils::getPath($this->kernel->getProjectDir(), $type->value, $this->fileName));
        $this->filesystem->appendToFile(
            PhotoUtils::getPath($this->kernel->getProjectDir(), $type->value, $this->fileName),
            $this->image->getImageBlob()
        );
    }
}
