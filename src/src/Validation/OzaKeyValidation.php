<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\OzaKey;
use RuntimeException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;

class OzaKeyValidation extends BaseValidation
{
    public function getDto(): OzaKey
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new OzaKey($this->data['key'] ?? null);
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'key' => new Optional([
                    new Sequentially([
                        new Type('string'),
                        new Length(['max' => LengthConfig::OZA_KEY]),
                    ]),
                ]),
            ],
            allowExtraFields: false,
        );
    }

    protected function normalizeData(): void
    {
        if (!isset($this->data['key']) || !is_string($this->data['key'])) {
            return;
        }

        $this->data['key'] = trim($this->data['key']);
    }
}
