<?php

namespace App\Validator;

use App\Utils\FormUtils;
use Symfony\Component\Validator\Context\ExecutionContext;

class DifferentPassword
{
    private ExecutionContext $context;

    public function __construct(ExecutionContext $context)
    {
        $this->context = $context;
    }

    public function validate(?string $value): void
    {
        if ($value === null) {
            return;
        }
        $form = FormUtils::getParentForm($this->context);
        $oldPassword = $form->get('oldPassword');
        if (!$oldPassword->isValid()) {
            return;
        }
        if ($value !== $oldPassword->getData()) {
            return;
        }
        $this->context->buildViolation('')->addViolation();
    }
}
