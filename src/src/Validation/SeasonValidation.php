<?php

namespace App\Validation;

use App\Dto\Request\Season;
use App\Entity\Season as SeasonEntity;
use App\Repository\IngredientRepository;
use App\Repository\SeasonRepository;
use App\Service\UserService;
use App\ValidatorRule\ExistsForUser\ExistsForUser;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SeasonValidation extends EditSeasonValidation
{
    private IngredientRepository $ingredientRepository;
    private SeasonRepository $seasonRepository;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserService $userService,
        IngredientRepository $ingredientRepository,
        SeasonRepository $seasonRepository,
    ) {
        parent::__construct($validator, $requestStack, $userService);
        $this->ingredientRepository = $ingredientRepository;
        $this->seasonRepository = $seasonRepository;
    }

    public function getDto(): Season
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new Season(
            $this->ingredientRepository->findById($this->data['ingredient'], $this->user),
            $this->data['start'],
            $this->data['stop'],
        );
    }

    public function uniqueIngredient(int $value, ExecutionContextInterface $context): void
    {
        $season = $this->seasonRepository->findById($value, $this->user);
        if (!$season instanceof SeasonEntity) {
            return;
        }

        $context->buildViolation('Ingredient is already in use.')->addViolation();
    }

    protected function getRules(): Collection
    {
        $rules = parent::getRules();
        $fields = $rules->fields ?? [];
        $fields['ingredient'] = new Sequentially([
            new NotBlank(),
            new Type('int'),
            new ExistsForUser($this->ingredientRepository, $this->user),
            new Callback([$this, 'uniqueIngredient']),
        ]);

        return new Collection($fields, allowExtraFields: false);
    }
}
