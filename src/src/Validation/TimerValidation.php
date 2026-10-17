<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\Timer;
use RuntimeException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;

class TimerValidation extends BaseValidation
{
    public function getDto(): Timer
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new Timer($this->data['name'] ?? null, $this->data['time']);
    }

    public function getRules(): Collection
    {
        return new Collection(
            [
                'name' => new Optional([
                    new Sequentially([
                        new Type('string'),
                        new Length(['max' => LengthConfig::TIMER]),
                    ]),
                ]),
                'time' => new Sequentially([
                    new NotBlank(),
                    new Type('int'),
                    new Positive(),
                ]),
            ],
            allowExtraFields: false,
        );
    }

    protected function normalizeData(): void
    {
        if (!isset($this->data['name']) || !is_string($this->data['name'])) {
            return;
        }

        $this->data['name'] = trim($this->data['name']);
    }
}
