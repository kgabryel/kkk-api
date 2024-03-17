<?php

namespace App\Utils;

use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Context\ExecutionContext;

class FormUtils
{
    public static function getParentForm(ExecutionContext $context): Form
    {
        return $context->getObject()->getParent();
    }
}
