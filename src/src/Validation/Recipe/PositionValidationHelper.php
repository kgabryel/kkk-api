<?php

namespace App\Validation\Recipe;

use App\Dto\Request\RecipePosition;
use App\Entity\User;
use App\Repository\IngredientRepository;
use App\Repository\RecipeRepository;
use App\Service\UserService;
use App\ValidationPolicy\RequiredBool;
use App\ValidationPolicy\RequiredString;
use App\ValidatorRule\ExistsForUser\ExistsForUser;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PositionValidationHelper
{
    private IngredientRepository $ingredientRepository;
    private RecipeRepository $recipeRepository;
    private User $user;

    public function __construct(
        IngredientRepository $ingredientRepository,
        RecipeRepository $recipeRepository,
        UserService $userService,
    ) {
        $this->ingredientRepository = $ingredientRepository;
        $this->recipeRepository = $recipeRepository;
        $this->user = $userService->getUser();
    }

    public function getDto(array $data): RecipePosition
    {
        $ingredient = ($data['ingredient'] ?? null) === null ? null : $this->ingredientRepository->findById(
            $data['ingredient'],
            $this->user,
        );
        $recipe = ($data['recipe'] ?? null) === null ? null : $this->recipeRepository->findById(
            $data['recipe'],
            $this->user,
        );

        return new RecipePosition(
            $data['additional'],
            $data['amount'] ?? null,
            $data['measure'],
            $ingredient,
            $recipe,
        );
    }

    public function getRules(): Sequentially
    {
        return new Sequentially([
            new Collection(
                [
                    'additional' => new RequiredBool(),
                    'amount' => new Optional([
                        new Sequentially([
                            new Type(['float', 'int']),
                            new GreaterThan([
                                'value' => 0,
                            ]),
                        ]),
                    ]),
                    'ingredient' => new Optional([
                        new Sequentially([
                            new Type('int'),
                            new ExistsForUser($this->ingredientRepository, $this->user),
                        ]),
                    ]),
                    'measure' => new RequiredString(100),
                    'recipe' => new Optional([
                        new Sequentially([
                            new Type('int'),
                            new ExistsForUser($this->recipeRepository, $this->user),
                        ]),
                    ]),
                ],
                allowExtraFields: false,
            ),
            new Callback([$this, 'onlyOnePositionFilled']),
        ]);
    }

    public function onlyOnePositionFilled(array $data, ExecutionContextInterface $context): void
    {
        $isIngredientFilled = ($data['ingredient'] ?? null) !== null;
        $isRecipeFilled = ($data['recipe'] ?? null) !== null;
        if ($isIngredientFilled !== $isRecipeFilled) {
            return;
        }

        $context->buildViolation('Exactly one of "ingredient" or "recipe" must be set.')->addViolation();
    }
}
