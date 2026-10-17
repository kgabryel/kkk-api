<?php

namespace App\Service\Photo;

use App\Config\PhotoType;
use App\Utils\PhotoUtils;
use Imagick;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class PhotoStorage
{
    private Filesystem $filesystem;
    private string $projectDir;

    public function __construct(Filesystem $filesystem, KernelInterface $kernel)
    {
        $this->filesystem = $filesystem;
        $this->projectDir = $kernel->getProjectDir();
    }

    public function saveFile(PhotoType $type, Imagick $image, string $fileName): void
    {
        $this->filesystem->dumpFile(PhotoUtils::getPath($this->projectDir, $type, $fileName), $image->getImageBlob());
    }
}
