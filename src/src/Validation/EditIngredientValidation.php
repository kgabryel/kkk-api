<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\EditIngredient;
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

class EditIngredientValidation extends BaseValidation
{
    private int $expect;
    private IngredientRepository $ingredientRepository;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserService $userService,
        IngredientRepository $ingredientRepository,
    ) {
        parent::__construct($validator, $requestStack, $userService);
        $this->ingredientRepository = $ingredientRepository;
        $this->expect = 0;
    }

    public function getDto(): EditIngredient
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new EditIngredient(
            $this->data['name'] ?? null,
            $this->data['available'] ?? null,
            $this->data['ozaId'] ?? null,
        );
    }

    public function setExpect(int $expect): void
    {
        $this->expect = $expect;
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                 'available' => new Optional([
                     new Type('bool'),
                 ]),
                 'name' => new Optional(
                     new Sequentially([
                         new RequiredString(LengthConfig::INGREDIENT),
                         new UniqueNameForUser($this->ingredientRepository, $this->user, 'name', $this->expect),
                     ]),
                 ),
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
