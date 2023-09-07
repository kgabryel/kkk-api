<?php

namespace App\Validator\ExistsForUser;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExistsForUserValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /* @var $constraint ExistsForUser */
        if ($value === null) {
            return;
        }
        $repository = $constraint->getRepository();
        $entity = $repository->findById($value, $constraint->getUser());
        if ($entity !== null) {
            return;
        }
        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
