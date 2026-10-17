<?php

namespace App\Validation;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class Result
{
    private array $errors;
    private ConstraintViolationListInterface $rawErrors;

    public function __construct(ConstraintViolationListInterface $errors)
    {
        $this->errors = [];
        $this->rawErrors = $errors;
        foreach ($errors as $error) {
            $this->errors[$error->getPropertyPath()][] = $error->getMessage();
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRawErrors(): ConstraintViolationListInterface
    {
        return $this->rawErrors;
    }

    public function passed(): bool
    {
        return $this->errors === [];
    }
}
