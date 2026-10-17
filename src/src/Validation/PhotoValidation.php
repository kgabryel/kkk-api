<?php

namespace App\Validation;

use App\Dto\Request\Photo;
use App\ValidationPolicy\RequiredString;
use RuntimeException;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PhotoValidation extends BaseValidation
{
    public function correctBase64(string $value, ExecutionContextInterface $context): void
    {
        $position = strpos($value, 'base64,');
        if ($position === false) {
            $context->buildViolation('Invalid Base64 string - prefix.')->addViolation();

            return;
        }

        $base64 = substr($value, $position + strlen('base64,'));
        if (base64_decode($base64, true)) {
            return;
        }

        $context->buildViolation('Invalid Base64 string - content.')->addViolation();
    }

    public function getDto(): Photo
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        $position = strpos($this->data['photo'], 'base64,');
        $base64 = substr($this->data['photo'], $position + strlen('base64,'));

        return new Photo(base64_decode($base64));
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'photo' => new Sequentially([
                    new RequiredString(),
                    new Callback([$this, 'correctBase64']),
                ]),
            ],
            allowExtraFields: false,
        );
    }
}
