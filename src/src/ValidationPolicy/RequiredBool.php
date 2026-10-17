<?php

namespace App\ValidationPolicy;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;

class RequiredBool extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Sequentially([
                new NotNull(),
                new Type('bool'),
            ]),
        ];
    }
}
