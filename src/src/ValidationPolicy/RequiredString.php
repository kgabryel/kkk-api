<?php

namespace App\ValidationPolicy;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;

class RequiredString extends Compound
{
    private ?int $maxLength;

    public function __construct(?int $maxLength = null)
    {
        $this->maxLength = $maxLength;
        parent::__construct();
    }

    protected function getConstraints(array $options): array
    {
        $rules = [
            new NotBlank(),
            new Type('string'),
        ];
        if ($this->maxLength !== null) {
            $rules[] = new Length(['max' => $this->maxLength]);
        }

        return [
            new Sequentially($rules),
        ];
    }
}
