<?php

namespace App\Utils;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class MonologDatabaseHandler
{
    private EntityManagerInterface $entityManager;
    private ManagerRegistry $managerRegistry;

    public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $managerRegistry)
    {
        $this->entityManager = $entityManager;
        $this->managerRegistry = $managerRegistry;
    }

    public function write(string $message, string $context = '', string $extra = ''): void
    {
        $logEntry = new Log();
        $logEntry->setMessage($message);
        $logEntry->setExtra($extra);
        $logEntry->setContext($context);
        $this->managerRegistry->resetManager();
        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();
    }
}
