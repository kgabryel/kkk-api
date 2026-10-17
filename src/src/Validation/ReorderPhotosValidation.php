<?php

namespace App\Validation;

use App\Dto\Request\List\OrderList;
use App\Dto\Request\Order;
use App\Entity\Photo;
use App\Entity\Recipe;
use App\Entity\User;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReorderPhotosValidation
{
    protected array $data;
    protected RequestStack $requestStack;
    protected User $user;
    protected bool $validate;
    protected ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, RequestStack $requestStack)
    {
        $this->validate = false;
        $this->validator = $validator;
        $this->data = [];
        $this->requestStack = $requestStack;
    }

    public function getDto(): OrderList
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new OrderList(
            ...array_map(
                static fn (array $data): Order => new Order(
                    $data['id'],
                    $data['index'],
                ),
                $this->data['order'],
            ),
        );
    }

    public function uniqueIds(array $value, ExecutionContextInterface $context): void
    {
        $this->unique($value, $context, 'id');
    }

    public function uniqueIndexes(array $value, ExecutionContextInterface $context): void
    {
        $this->unique($value, $context, 'index');
    }

    public function validate(Recipe $recipe): Result
    {
        try {
            $this->data = $this->requestStack->getCurrentRequest()?->toArray() ?? [];
        } catch (JsonException) {
            $this->data = [];
        }
        $result = new Result($this->validator->validate($this->data, $this->getRules($recipe)));
        $this->validate = $result->passed();

        return $result;
    }

    protected function getRules(Recipe $recipe): Collection
    {
        $photos = $recipe->getPhotos()->map(static fn (Photo $photo): int => $photo->getId());

        return new Collection(
            [
                'order' => new Sequentially([
                    new Count($recipe->getPhotos()->count()),
                    new All(
                        new Collection(
                            [
                                'id' => new Sequentially([
                                    new Type('int'),
                                    new Choice([
                                        'choices' => $photos->toArray(),
                                    ]),
                                ]),
                                'index' => new Sequentially([
                                    new Type('int'),
                                ]),
                            ],
                            allowExtraFields: false,
                        ),
                    ),
                    new Callback([$this, 'uniqueIds']),
                    new Callback([$this, 'uniqueIndexes']),
                ]),
            ],
            allowExtraFields: false,
        );
    }

    private function unique(array $value, ExecutionContextInterface $context, string $column): void
    {
        $uniqueCount = count(array_unique(array_map(static fn (array $pair) => $pair[$column], $value)));
        if ($uniqueCount === count($value)) {
            return;
        }

        $context->buildViolation(sprintf('All values in (%s) must be unique.', $column))->addViolation();
    }
}
