<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\ResetPasswordRequest;
use App\ValidationPolicy\RequiredString;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordRequestValidation
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

    public function getDto(): ResetPasswordRequest
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new ResetPasswordRequest($this->data['email']);
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

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'email' => new Sequentially([
                    new RequiredString(LengthConfig::EMAIL),
                    new Email(['mode' => Email::VALIDATION_MODE_STRICT]),
                ]),
            ],
            allowExtraFields: false,
        );
    }

    protected function normalizeData(): void
    {
        if (!isset($this->data['email']) || !is_string($this->data['email'])) {
            return;
        }

        $this->data['email'] = trim($this->data['email']);
    }
}
