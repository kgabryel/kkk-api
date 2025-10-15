<?php

namespace App\Validation\Recipe;

use App\Config\LengthConfig;
use App\Dto\List\Type\StringList;
use App\Dto\Request\List\RecipePositionList;
use App\Dto\Request\List\RecipePositionsGroupList;
use App\Dto\Request\List\TimerList;
use App\Dto\Request\Recipe;
use App\Dto\Request\RecipePosition;
use App\Dto\Request\RecipePositionsGroup;
use App\Dto\Request\Timer;
use App\Service\UserService;
use App\Validation\BaseValidation;
use App\Validation\TimerValidation;
use App\ValidationPolicy\RequiredBool;
use App\ValidationPolicy\RequiredString;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecipeValidation extends BaseValidation
{
    private PositionValidationHelper $positionsValidation;
    private TimerValidation $timerValidation;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserService $userService,
        PositionValidationHelper $positionsValidation,
        TimerValidation $timerValidation,
    ) {
        parent::__construct($validator, $requestStack, $userService);
        $this->positionsValidation = $positionsValidation;
        $this->timerValidation = $timerValidation;
    }

    public function getDto(): Recipe
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new Recipe(
            $this->data['name'],
            $this->data['description'] ?? null,
            $this->data['url'] ?? null,
            $this->data['portions'] ?? null,
            $this->data['favourite'],
            $this->data['public'],
            $this->data['toDo'],
            $this->getTags($this->data['tags'] ?? []),
            $this->getTimers($this->data['timers'] ?? []),
            $this->getGroupList($this->data['groups'] ?? []),
        );
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'description' => new Optional([new Type('string')]),
                'favourite' => new RequiredBool(),
                'groups' => new Optional([
                    new All(
                        new Collection(
                            [
                                'name' => new Optional([
                                    new Sequentially([
                                        new Type('string'),
                                        new Length([
                                            'max' => 255,
                                        ]),
                                    ]),
                                ]),
                                'positions' => new Sequentially([
                                    new Count(min: 1),
                                    new All($this->positionsValidation->getRules()),
                                ]),
                            ],
                            allowExtraFields: false,
                        ),
                    ),
                ]),
                'name' => new RequiredString(LengthConfig::RECIPE),
                'portions' => new Sequentially([
                    new NotBlank(),
                    new Type('int'),
                    new GreaterThan([
                        'value' => 0,
                    ]),
                ]),
                'public' => new RequiredBool(),
                'tags' => new Optional(new All(new RequiredString(LengthConfig::TAG))),
                'timers' => new Optional(new All($this->timerValidation->getRules())),
                'toDo' => new RequiredBool(),
                'url' => new Optional([
                    new Sequentially([
                        new Url(requireTld: true),
                    ]),
                ]),
            ],
            allowExtraFields: false,
        );
    }

    private function getGroupList(array $groups): RecipePositionsGroupList
    {
        return new RecipePositionsGroupList(
            ...array_map(
                fn ($group): RecipePositionsGroup => new RecipePositionsGroup(
                    $group['name'] ?? null,
                    new RecipePositionList(
                        ...array_map(
                            fn ($position): RecipePosition => $this->positionsValidation->getDto($position),
                            $group['positions'] ?? [],
                        ),
                    ),
                ),
                $groups,
            ),
        );
    }

    private function getTags(array $data): StringList
    {
        return new StringList(
            ...array_unique(array_map(static fn (string $tag) => strtoupper($tag), $data)),
        );
    }

    private function getTimers(array $data): TimerList
    {
        return new TimerList(
            ...array_map(static fn ($timer): Timer => new Timer($timer['name'] ?? null, $timer['time']), $data),
        );
    }
}
