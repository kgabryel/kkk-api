<?php

namespace App\Service\Entity;

use App\Config\PhotoType;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\PhotoRepository;
use App\Service\UserService;
use App\Utils\PhotoUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class PhotoService extends EntityService
{
    private Filesystem $filesystem;
    private KernelInterface $kernel;
    private PhotoRepository $photoRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        PhotoRepository $photoRepository,
        Filesystem $filesystem,
        KernelInterface $kernel,
    ) {
        parent::__construct($entityManager, $userService);
        $this->photoRepository = $photoRepository;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    public function checkAccess(Photo $photo, ?User $user): bool
    {
        $recipe = $photo->getRecipe();
        if ($recipe->isPublic()) {
            return true;
        }

        $photoUser = $photo->getUser();

        return $photoUser->getId() === $user?->getId();
    }

    public function find(int $id): ?Photo
    {
        return $this->photoRepository->findById($id, $this->user);
    }

    public function remove(Photo $photo): void
    {
        $fileName = $photo->getFileName();
        $this->removeEntity($photo);
        foreach (PhotoType::cases() as $type) {
            $this->filesystem->remove(PhotoUtils::getPath($this->kernel->getProjectDir(), $type, $fileName));
        }
    }
}
