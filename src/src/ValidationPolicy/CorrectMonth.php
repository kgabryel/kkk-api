<?php

namespace App\ValidationPolicy;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;

class CorrectMonth extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Sequentially([
                new NotBlank(),
                new Type('int'),
                new Range([
                    'max' => 12,
                    'min' => 1,
                ]),
            ]),
        ];
    }
}
