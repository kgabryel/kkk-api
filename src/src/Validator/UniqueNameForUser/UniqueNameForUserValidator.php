<?php

namespace App\Validator\UniqueNameForUser;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNameForUserValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /* @var $constraint UniqueNameForUser */
        $repository = $constraint->getRepository();
        $entity = $repository->findOneByNameWithLowercase(
            $constraint->getUser(),
            $constraint->getColumn(),
            $value
        );
        if ($entity === null) {
            return;
        }
        if ($entity->getId() === $constraint->getExpect()) {
            return;
        }
        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
