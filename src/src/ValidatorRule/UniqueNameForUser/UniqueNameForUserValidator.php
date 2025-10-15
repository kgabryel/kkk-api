<?php

namespace App\ValidatorRule\UniqueNameForUser;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNameForUserValidator extends ConstraintValidator
{
    /**
     * @param UniqueNameForUser $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!is_string($value)) {
            return;
        }

        $repository = $constraint->getRepository();
        $entity = $repository->findOneByNameWithLowercase($constraint->getUser(), $constraint->getColumn(), $value);
        if ($entity === null) {
            return;
        }

        if ($entity->getId() === $constraint->getExpect()) {
            return;
        }

        $this->context->buildViolation('This name is already used.')->addViolation();
    }
}
