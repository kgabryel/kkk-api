<?php

namespace App\Response;

use App\Dto\Entity\Tag as Dto;
use App\Entity\Tag;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class TagResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Tag $tag, int $status)
    {
        parent::__construct($dtoFactory->get(Dto::class, $tag), $status);
    }
}
