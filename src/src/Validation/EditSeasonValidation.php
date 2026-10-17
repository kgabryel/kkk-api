<?php

namespace App\Validation;

use App\Dto\Request\EditSeason;
use App\ValidationPolicy\CorrectMonth;
use RuntimeException;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class EditSeasonValidation extends BaseValidation
{
    public function getDto(): EditSeason
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new EditSeason($this->data['start'], $this->data['stop']);
    }

    public function validateSeasonRange(int $value, ExecutionContextInterface $context): void
    {
        $violations = $context->getViolations();
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            if ($path === '[start]') {
                return;
            }
        }

        $data = $context->getRoot();
        $start = $data['start'];
        if ($value > $start) {
            return;
        }

        $context->buildViolation('Stop must be greater than start.')->addViolation();
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'start' => new CorrectMonth(),
                'stop' => new Sequentially([
                    new CorrectMonth(),
                    new Callback([$this, 'validateSeasonRange']),
                ]),
            ],
            allowExtraFields: false,
        );
    }
}
