<?php

namespace App\ValidatorRule\ExistsForUser;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExistsForUserValidator extends ConstraintValidator
{
    /**
     * @param ExistsForUser $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $repository = $constraint->getRepository();
        if ($value === null) {
            return;
        }
        $entity = $repository->findById((int)$value, $constraint->getUser());
        if ($entity !== null) {
            return;
        }

        $this->context->buildViolation('No matching item found for this user.')->addViolation();
    }
}
