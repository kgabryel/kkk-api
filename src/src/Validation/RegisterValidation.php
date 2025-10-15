<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\User;
use App\Repository\UserRepository;
use App\ValidationPolicy\RequiredString;
use App\ValidatorRule\PasswordsMatch\PasswordsMatch;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterValidation
{
    protected RequestStack $requestStack;
    protected bool $validate;
    protected ValidatorInterface $validator;
    private array $data;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->validate = false;
        $this->validator = $validator;
        $this->requestStack = $requestStack;
        $this->data = [];
    }

    public function getDto(): User
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new User($this->passwordHasher, $this->data['email'], $this->data['password']['first']);
    }

    public function uniqueEmail(string $value, ExecutionContextInterface $context): void
    {
        $user = $this->userRepository->findOneBy([
            'email' => $value,
            'fbId' => null,
        ]);
        if ($user === null) {
            return;
        }

        $context->buildViolation('Email is already in use.')->addViolation();
    }

    public function validate(): Result
    {
        try {
            $this->data = $this->requestStack->getCurrentRequest()?->toArray() ?? [];
        } catch (JsonException) {
            $this->data = [];
        }
        if (isset($this->data['email']) && is_string($this->data['email'])) {
            $this->data['email'] = trim($this->data['email']);
        }
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
                    new Callback([$this, 'uniqueEmail']),
                ]),
                'password' => new Collection(
                    [
                        'first' => new RequiredString(LengthConfig::PASSWORD),
                        'second' => new Sequentially([
                            new NotBlank(),
                            new PasswordsMatch('password'),
                        ]),
                    ],
                ),
            ],
            allowExtraFields: false,
        );
    }
}
