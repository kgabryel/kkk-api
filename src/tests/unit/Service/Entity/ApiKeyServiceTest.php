<?php

namespace App\Tests\Unit\Service\Entity;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Service\Entity\ApiKeyService;
use App\Service\UserService;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\FindEntityDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(ApiKeyService::class)]
#[CoversClass(ApiKey::class)]
class ApiKeyServiceTest extends BaseTestCase
{
    private ApiKey $apiKey;
    private ApiKeyService $apiKeyService;
    private User $user;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->apiKey = EntityFactory::getSimpleApiKey();
    }

    #[Test]
    #[TestDox('Zwraca encję (ApiKey) użytkownika znalezioną w repozytorium')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'apiKeyValues')]
    public function itFindsApiKey(int $id, ?ApiKey $apiKey): void
    {
        // Arrange
        $this->init(
            apiKeyRepository: $this->getMock(
                ApiKeyRepository::class,
                new AllowedMethod('findById', $apiKey, $this->once(), [$id, $this->user]),
            ),
        );

        // Act
        $result = $this->apiKeyService->find($id);

        // Assert
        $this->assertSame($apiKey, $result);
    }

    #[Test]
    #[TestDox('Usuwa encję (ApiKey) z bazy danych')]
    public function itRemovesApiKey(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('remove', $this->once(), [$this->apiKey]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->apiKeyService->remove($this->apiKey);
    }

    #[Test]
    #[TestDox('Zamienia aktywność klucza z true na false i na odwrót')]
    #[DataProviderExternal(CommonDataProvider::class, 'oppositeBoolValues')]
    public function itTogglesKeyState(bool $preview, bool $expected): void
    {
        // Arrange
        if ($preview) {
            $this->apiKey->activate();
        } else {
            $this->apiKey->deactivate();
        }
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('persist', $this->once(), [$this->apiKey]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->apiKeyService->switch($this->apiKey);

        // Assert
        $this->assertSame($expected, $this->apiKey->isActive());
    }

    private function init(
        ?EntityManagerInterface $entityManager = null,
        ?ApiKeyRepository $apiKeyRepository = null,
    ): void {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->apiKeyService = new ApiKeyService(
            $entityManager ?? $this->getMock(EntityManagerInterface::class),
            $userService,
            $apiKeyRepository ?? $this->getMock(ApiKeyRepository::class),
        );
    }
}
