<?php

namespace App\Validation;

use App\Dto\Request\RecipeFlags;
use RuntimeException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;

class RecipeFlagsValidation extends BaseValidation
{
    public function getDto(): RecipeFlags
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new RecipeFlags($this->data['favourite'] ?? null, $this->data['toDo'] ?? null);
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'favourite' => new Optional([
                    new Type('bool'),
                ]),
                'toDo' => new Optional([
                    new Type('bool'),
                ]),
            ],
            allowExtraFields: false,
        );
    }
}
