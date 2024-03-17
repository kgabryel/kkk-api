<?php

namespace App\Factory\Entity;

use App\Entity\ApiKey;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\String\ByteString;

class ApiKeyFactory extends EntityFactory
{
    public function generate(): ?ApiKey
    {
        $apiKey = new ApiKey();
        $apiKey->deactivate();
        $apiKey->setUser($this->user);
        $saved = false;
        $failCount = 0;
        while (!$saved && $failCount < 10) {
            $apiKey->setKey(ByteString::fromRandom(128)->toString());
            try {
                $this->saveEntity($apiKey);
                $saved = true;
            } catch (UniqueConstraintViolationException) {
                $failCount++;
            }
        }

        return $saved ? $apiKey : null;
    }
}
