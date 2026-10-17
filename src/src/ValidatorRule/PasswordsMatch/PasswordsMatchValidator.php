<?php

namespace App\ValidatorRule\PasswordsMatch;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordsMatchValidator extends ConstraintValidator
{
    /**
     * @param PasswordsMatch $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        $key = $constraint->getKey();
        $violations = $this->context->getViolations();
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if ($path === sprintf('[%s][first]', $key)) {
                return;
            }
        }

        $data = $this->context->getRoot();
        if (!is_array($data[$key] ?? null)) {
            return;
        }

        $firstInsert = $data[$key]['first'] ?? null;
        if ($firstInsert === null) {
            return;
        }

        if ($value === $firstInsert) {
            return;
        }

        $this->context->buildViolation('The password confirmation does not match.')->addViolation();
    }
}
