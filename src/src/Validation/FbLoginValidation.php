<?php

namespace App\Validation;

use App\Dto\Request\AuthToken;
use App\ValidationPolicy\RequiredString;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FbLoginValidation
{
    protected RequestStack $requestStack;
    protected bool $validate;
    protected ValidatorInterface $validator;
    private array $data;

    public function __construct(ValidatorInterface $validator, RequestStack $requestStack)
    {
        $this->validate = false;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->data = [];
    }

    public function getDto(): AuthToken
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new AuthToken(trim($this->data['authToken']));
    }

    public function validate(): Result
    {
        try {
            $this->data = $this->requestStack->getCurrentRequest()?->toArray() ?? [];
        } catch (JsonException) {
            $this->data = [];
        }
        $result = new Result($this->validator->validate($this->data, $this->getRules()));
        $this->validate = $result->passed();

        return $result;
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'authToken' => new RequiredString(),
            ],
            allowExtraFields: false,
        );
    }
}
