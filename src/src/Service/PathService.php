<?php

namespace App\Service;

use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PathService
{
    private string $frontUrl;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        if (!$parameterBag->has('FRONT_URL')) {
            throw new RuntimeException('FRONT_URL parameter is not set.');
        }

        $frontUrl = $parameterBag->get('FRONT_URL');
        if (empty($frontUrl) || !is_string($frontUrl)) {
            throw new RuntimeException('FRONT_URL parameter is empty or invalid.');
        }

        $this->frontUrl = trim($frontUrl, '/');
    }

    public function getPathToFront(string $path): string
    {
        return trim(sprintf('%s/%s', $this->frontUrl, trim($path, '/')), '/');
    }
}
