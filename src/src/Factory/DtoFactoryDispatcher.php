<?php

namespace App\Factory;

use App\Dto\BaseList;
use App\Dto\Entity\DtoInterface;
use App\Dto\Entity\List\DtoList;
use App\Factory\Dto\DtoFactoryInterface;
use InvalidArgumentException;

class DtoFactoryDispatcher
{
    /** @var DtoFactoryInterface[] */
    private array $factories;

    public function __construct(iterable $factories)
    {
        $this->factories = [];
        foreach ($factories as $factory) {
            if (!$factory instanceof DtoFactoryInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Factory must implement the "%s" interface. Given factory does not implement the required interface.',
                        DtoFactoryInterface::class,
                    ),
                );
            }

            $this->factories[$factory->getDtoName()] = $factory;
        }
    }

    public function get(string $dtoName, object $entity): DtoInterface
    {
        $factory = $this->factories[$dtoName] ?? null;
        if ($factory instanceof DtoFactoryInterface) {
            return $factory->get($entity, $this);
        }

        throw new InvalidArgumentException("Unsupported DTO type: $dtoName");
    }

    /**
     * @template T of DtoList&BaseList
     *
     * @param class-string<T> $listName
     *
     * @return T
     */
    public function getMany(string $listName, object ...$entities): DtoList&BaseList
    {
        $dtoName = $listName::getDtoName();
        $factory = $this->factories[$dtoName] ?? null;
        if ($factory instanceof DtoFactoryInterface) {
            return new $listName(...array_map(
                fn (object $entity): DtoInterface => $factory->get($entity, $this),
                $entities,
            ));
        }

        throw new InvalidArgumentException("Unsupported DTO type: $dtoName");
    }
}
