<?php

namespace App\Validator\CorrectPassword;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CorrectPasswordValidator extends ConstraintValidator
{
    /**
     * @param  mixed  $value
     * @param  CorrectPassword  $constraint
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint->getPasswordEncoder()->isPasswordValid($constraint->getUser(), $value ?? '')) {
            return;
        }
        $this->context->buildViolation('')->addViolation();
    }
}
