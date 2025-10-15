<?php

namespace App\Tests\Helper\TestCase;

use App\Entity\User;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\SetupAllowedMethodsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Zenstruck\Foundry\Test\Factories;

abstract class BaseIntegrationTestCase extends KernelTestCase
{
    use Factories;

    protected User $defaultUser;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadata);
        $this->defaultUser = EntityFactory::createUser(EntityFactory::USER_EMAIL);
    }

    protected function assertHasNoViolations(ConstraintViolationListInterface $violations): void
    {
        $this->assertCount(
            0,
            $violations,
            'Expected no violations, got ' . count($violations),
        );
    }

    protected function assertHasOnlyOneViolationWithMessage(
        ConstraintViolationListInterface $violations,
        string $expectedMessage,
    ): void {
        $this->assertCount(
            1,
            $violations,
            'Expected exactly one violation, got ' . count($violations),
        );
        $this->assertSame(
            $expectedMessage,
            $violations[0]->getMessage(),
            "Expected violation message '{$expectedMessage}', got '{$violations[0]->getMessage()}'",
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T&MockObject
     */
    protected function getMock(string $className, AllowedMethod ...$allowedMethods): object
    {
        $mock = $this->createMock($className);
        $setupAllowedMethodHelper = new SetupAllowedMethodsHelper($this->never());
        $setupAllowedMethodHelper->setupAllowedMethods($mock, $className, ...$allowedMethods);

        return $mock;
    }

    protected function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
