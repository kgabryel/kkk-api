<?php

namespace App\Validator\UniqueEmail;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEmailValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /* @var $constraint UniqueEmail */
        if (null === $value) {
            return;
        }
        $user = $constraint->getRepository()
            ->findOneBy([
                'email' => $value,
                'fbId' => null
            ]);
        if ($user === null) {
            return;
        }
        $this->context->buildViolation('')->addViolation();
    }
}
