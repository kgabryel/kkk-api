<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\Password;
use App\Service\UserService;
use App\ValidationPolicy\RequiredString;
use App\ValidatorRule\PasswordsMatch\PasswordsMatch;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangePasswordValidation extends BaseValidation
{
    /*
     * Obejście dla AlphabeticallySortedByKeys żeby równocześnie działało sortowanie kluczy dla tego pliku i walidacja
     * Symfony wykonuje walidację według kolejności przekazanych pól, a walidacja newPassword bazuje na
     * walidacji pola oldPassword. AlphabeticallySortedByKeys zmienia kolejność na newPassword -> oldPassword
     * przez co walidacja przestaje działać poprawnie
     */
    private const string A_OLD_PASSWORD = 'oldPassword';
    private const string B_NEW_PASSWORD = 'newPassword';
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserService $userService,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct($validator, $requestStack, $userService);
        $this->passwordHasher = $passwordHasher;
    }

    public function checkPassword(string $value, ExecutionContextInterface $context): void
    {
        if ($this->passwordHasher->isPasswordValid($this->user, $value)) {
            return;
        }

        $context->buildViolation('Invalid password.')->addViolation();
    }

    public function getDto(): Password
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new Password($this->passwordHasher, $this->user, $this->data['newPassword']['first']);
    }

    public function passwordChanged(string $value, ExecutionContextInterface $context): void
    {
        $violations = $context->getViolations();

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if ($path === '[oldPassword]') {
                return;
            }
        }

        $data = $context->getRoot();
        $oldPassword = $data['oldPassword'];

        if (!isset($data['oldPassword'])) {
            return;
        }

        if ($value !== $oldPassword) {
            return;
        }

        $context->buildViolation('New password must be different from old password.')->addViolation();
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                self::A_OLD_PASSWORD => new Sequentially([
                    new RequiredString(LengthConfig::PASSWORD),
                    new Callback([$this, 'checkPassword']),
                ]),
                self::B_NEW_PASSWORD => new Collection(
                    [
                        'first' => new Sequentially([
                            new RequiredString(LengthConfig::PASSWORD),
                            new Callback([$this, 'passwordChanged']),
                        ]),
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
