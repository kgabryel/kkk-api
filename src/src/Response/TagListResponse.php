<?php

namespace App\Response;

use App\Dto\Entity\List\TagList;
use App\Entity\Tag;
use App\Factory\DtoFactoryDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;

class TagListResponse extends JsonResponse
{
    public function __construct(DtoFactoryDispatcher $dtoFactory, Tag ...$tags)
    {
        parent::__construct($dtoFactory->getMany(TagList::class, ...$tags));
    }
}
