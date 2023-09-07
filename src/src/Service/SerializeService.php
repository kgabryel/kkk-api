<?php

namespace App\Service;

use App\Dto\DtoInterface;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializeService
{
    public const DATA_FORMAT = 'json';
    private Serializer $serializer;
    private string $dtoName;

    private function __construct(Serializer $serializer, string $dtoName)
    {
        $this->serializer = $serializer;
        $this->dtoName = $dtoName;
    }

    public static function getInstance(string $dtoName): self
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $class = new ReflectionClass($dtoName);
        if (!$class->implementsInterface(DtoInterface::class)) {
            throw new InvalidArgumentException(
                printf(
                    'Class "%s" doesn\'t implements "%s" interface',
                    $dtoName,
                    DtoInterface::class
                )
            );
        }

        return new self(new Serializer($normalizers, $encoders), $dtoName);
    }

    public function serializeArray(array $entities): string
    {
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->dtoName::createFromEntity($entity);
        }

        return $this->serializer->serialize($result, self::DATA_FORMAT);
    }

    public function serialize(object $entity): string
    {
        return $this->serializer->serialize(
            $this->dtoName::createFromEntity($entity),
            self::DATA_FORMAT
        );
    }
}
