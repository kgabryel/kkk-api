<?php

namespace App\Validation;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseValidation
{
    protected array $data;
    protected RequestStack $requestStack;
    protected User $user;
    protected bool $validate;
    protected ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, RequestStack $requestStack, UserService $userService)
    {
        $this->validate = false;
        $this->validator = $validator;
        $this->data = [];
        $this->user = $userService->getUser();
        $this->requestStack = $requestStack;
    }

    public function validate(): Result
    {
        try {
            $this->data = $this->requestStack->getCurrentRequest()?->toArray() ?? [];
        } catch (JsonException) {
            $this->data = [];
        }
        $this->normalizeData();
        $result = new Result($this->validator->validate($this->data, $this->getRules()));
        $this->validate = $result->passed();

        return $result;
    }

    abstract protected function getRules(): Collection;

    protected function normalizeData(): void
    {
    }
}
