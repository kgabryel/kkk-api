<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\Password;
use App\Entity\User;
use App\ValidationPolicy\RequiredString;
use App\ValidatorRule\PasswordsMatch\PasswordsMatch;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetPasswordValidation
{
    protected RequestStack $requestStack;
    protected bool $validate;
    protected ValidatorInterface $validator;
    private array $data;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->validate = false;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->passwordHasher = $passwordHasher;
        $this->data = [];
    }

    public function getDto(User $user): Password
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new Password($this->passwordHasher, $user, $this->data['newPassword']['first']);
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
                'newPassword' => new Collection(
                    [
                         'first' => new RequiredString(LengthConfig::PASSWORD),
                         'second' => new Sequentially([
                             new NotBlank(),
                             new PasswordsMatch('newPassword'),
                         ]),
                     ],
                ),
            ],
            allowExtraFields: false,
        );
    }
}
