<?php

namespace App\Dto\Helper;

use App\Dto\List\Entity\PhotoList;
use App\Dto\List\Entity\RecipePositionGroupList;
use App\Dto\List\Entity\TagList;
use App\Dto\List\Entity\TimerList;

class RecipeRelatedEntities
{
    private RecipePositionGroupList $groupsList;
    private PhotoList $photosList;
    private TagList $tags;
    private TimerList $timersList;

    public function __construct(
        TagList $tags,
        RecipePositionGroupList $groupsList,
        TimerList $timersList,
        PhotoList $photosList,
    ) {
        $this->tags = $tags;
        $this->groupsList = $groupsList;
        $this->timersList = $timersList;
        $this->photosList = $photosList;
    }

    public function getGroupsList(): RecipePositionGroupList
    {
        return $this->groupsList;
    }

    public function getPhotosList(): PhotoList
    {
        return $this->photosList;
    }

    public function getTags(): TagList
    {
        return $this->tags;
    }

    public function getTimersList(): TimerList
    {
        return $this->timersList;
    }
}
