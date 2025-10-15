<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\CreateIngredient;
use App\Repository\IngredientRepository;
use App\Service\UserService;
use App\ValidationPolicy\RequiredString;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateIngredientValidation extends BaseValidation
{
    private IngredientRepository $ingredientRepository;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserService $userService,
        IngredientRepository $ingredientRepository,
    ) {
        parent::__construct($validator, $requestStack, $userService);
        $this->ingredientRepository = $ingredientRepository;
    }

    public function getDto(): CreateIngredient
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        $ozaId = $this->data['ozaId'] ?? 0;

        return new CreateIngredient($this->data['name'], $this->data['available'], $ozaId === 0 ? null : $ozaId);
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'available' => new Sequentially([
                    new Type('bool'),
                ]),
                'name' => new Sequentially([
                    new RequiredString(LengthConfig::INGREDIENT),
                    new UniqueNameForUser($this->ingredientRepository, $this->user, 'name'),
                ]),
                'ozaId' => new Optional([
                    new Type('int'),
                ]),
            ],
            allowExtraFields: false,
        );
    }

    protected function normalizeData(): void
    {
        if (!isset($this->data['name']) || !is_string($this->data['name'])) {
            return;
        }

        $this->data['name'] = trim($this->data['name']);
    }
}
