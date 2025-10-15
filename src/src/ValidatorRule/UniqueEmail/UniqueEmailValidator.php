<?php

namespace App\ValidatorRule\UniqueEmail;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator
{
    /**
     * @param UniqueEmail $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        $user = $constraint->getRepository()
            ->findOneBy([
                'email' => $value,
                'fbId' => null,
            ]);
        if ($user === null) {
            return;
        }

        $this->context->buildViolation('This email is already in use.')->addViolation();
    }
}
