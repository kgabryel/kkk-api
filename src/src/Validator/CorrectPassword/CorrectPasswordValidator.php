<?php

namespace App\Validator\CorrectPassword;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CorrectPasswordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /* @var $constraint CorrectPassword */
        if ($constraint->getPasswordEncoder()->isPasswordValid($constraint->getUser(), $value ?? '')) {
            return;
        }
        $this->context->buildViolation('')->addViolation();
    }
}
