<?php

namespace App\Validator\UniqueNameForUser;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueNameForUserValidator extends ConstraintValidator
{
    /**
     * @param  mixed  $value
     * @param  UniqueNameForUser  $constraint
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
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
        $this->context->buildViolation('')->addViolation();
    }
}
