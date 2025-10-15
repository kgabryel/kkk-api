<?php

namespace App\Tests\Functional;

use App\Controller\UserController;
use App\Dto\Entity\ApiKey;
use App\Dto\Entity\Settings;
use App\Entity\ApiKey as ApiKeyEntity;
use App\Entity\Settings as SettingsEntity;
use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ControllerDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\EntityTestDataDto;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Large]
#[CoversClass(UserController::class)]
#[CoversClass(Settings::class)]
#[CoversClass(SettingsEntity::class)]
#[CoversClass(User::class)]
#[CoversClass(ApiKeyEntity::class)]
#[CoversClass(ApiKey::class)]
#[CoversClass(ApiKeyRepository::class)]
class UserControllerTest extends BaseFunctionalTestCase
{
    private ApiKeyRepository $apiKeyRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiKeyRepository = self::getContainer()->get(ApiKeyRepository::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    #[Test]
    #[TestDox('Zmienia hasło gdy przesłano poprawne dane')]
    public function itChangesPassword(): void
    {
        // Arrange
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $hasher->hashPassword(new User(), 'password');
        $token = $this->initUser(EntityFactory::USER_EMAIL_2, ['fbId' => null, 'password' => $hashedPassword]);
        $data = [
            'newPassword' => [
                'first' => 'new-password',
                'second' => 'new-password',
            ],
            'oldPassword' => 'password',
        ];

        // Act
        $this->sendAuthorizedJsonRequest('POST', '/api/settings/change-password', $data, $token);

        // Refresh entities from DB
        $this->entityManager->refresh($this->user);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertTrue($hasher->isPasswordValid($this->user, 'new-password'));
    }

    #[Test]
    #[TestDox('Usuwa klucz OZA, zwraca ustawienia i 200, gdy dane są poprawne')]
    public function itClearOzaKeyWhenValid(): void
    {
        // Arrange
        $token = $this->initUser(EntityFactory::USER_EMAIL_2, [], ['ozaKey' => 'old', 'autocomplete' => true]);

        // Act
        $this->sendAuthorizedJsonRequest('PATCH', '/api/settings/change-oza-key', ['key' => null], $token);

        // Prepare expected
        $settingsData = [
            'autocomplete' => true,
            'ozaKey' => null,
            'userType' => 'standard',
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($settingsData);
    }

    #[Test]
    #[TestDox('Usuwa encję (ApiKey) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserApiKeyWhenAvailable(): void
    {
        // Arrange
        $apiKey = EntityFactory::createApiKey($this->user->getEmail());
        $apiKeyId = $apiKey->getId();

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/api-keys/%s', $apiKeyId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->apiKeyRepository->find($apiKeyId));
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać kluczy api')]
    public function itDeniesAccessToApiKeyWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/api-keys');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Facebook user nie może zmienić hasła')]
    public function itDeniesAccessToChangePasswordWhenFbUser(): void
    {
        // Arrange
        $token = $this->initUser(EntityFactory::USER_EMAIL_2, ['fbId' => 100]);

        // Act
        $this->sendAuthorizedRequest('POST', '/api/settings/change-password', $token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zmienić hasła')]
    public function itDeniesAccessToChangePasswordWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/settings/change-password');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może usunąć encji (ApiKey)')]
    public function itDeniesAccessToDeleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/api-keys/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może wygenerować klucza API')]
    public function itDeniesAccessToGenerateKeyWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/api-keys');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać swoich ustawień')]
    public function itDeniesAccessToSettingsWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/settings');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zmienić aktywności klucza API')]
    public function itDeniesAccessToSwitchKeyWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PATCH', '/api/api-keys/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zmienić wartości autocomplete')]
    public function itDeniesAccessToToggleAutocompleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PATCH', '/api/settings/switch-autocomplete');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zaktualizować klucza OZA')]
    public function itDeniesAccessToUpdateWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PATCH', '/api/settings/change-oza-key');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Zwraca ustawienia należące do użytkownika')]
    public function itGenerateApiKey(): void
    {
        // Act
        $this->sendAuthorizedRequest('POST', '/api/api-keys', $this->token);

        // Prepare expected
        $createdApiKey = $this->getLastCreatedApiKey();
        $apiKeyData = [
            'active' => $createdApiKey->isActive(),
            'id' => $createdApiKey->getId(),
            'key' => $createdApiKey->getKey(),
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponseEquals($apiKeyData);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (ApiKey) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsDeleteApiKeyWhenUnavailable(array $items): void
    {
        // Arrange
        $apiKeyId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): ApiKeyEntity => EntityFactory::createApiKey($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/api-keys/%s', $apiKeyId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie zmiany hasła i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnPasswordChange(): void
    {
        // Arrange
        $token = $this->initUser(EntityFactory::USER_EMAIL_2, ['fbId' => null]);

        // Act
        $this->sendAuthorizedRequest('POST', '/api/settings/change-password', $token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie rejestracji i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnRegister(): void
    {
        // Act
        $this->client->request('POST', '/api/register');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji OzaKey i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        EntityFactory::createSettings($this->user->getEmail());

        // Act
        $this->sendAuthorizedJsonRequest(
            'PATCH',
            '/api/settings/change-oza-key',
            ['key' => 123],
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Zwraca 404 gdy klucz API nie istnieje podczas zmiany aktywności klucza API')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsSwitchKeyWhenUnavailable(array $items): void
    {
        // Arrange
        $apiKeyId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): ApiKeyEntity => EntityFactory::createApiKey($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('PATCH', sprintf('/api/api-keys/%s', $apiKeyId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (ApiKey) należących do bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'apiKeysData')]
    public function itReturnsOnlyUserApiKeys(array $keys): void
    {
        // Arrange
        $expectedResponseData = $this->prepareExpectedIndexResponseData(
            $keys,
            static fn (EntityTestDataDto $apiKey): ApiKeyEntity => EntityFactory::createApiKey(
                $apiKey->getUserEmail(),
                $apiKey->getEntityData(),
            ),
            static fn (ApiKeyEntity $apiKey): array => [
                'active' => $apiKey->isActive(),
                'id' => $apiKey->getId(),
                'key' => $apiKey->getKey(),
            ],
        );

        // Act
        $this->sendAuthorizedRequest('GET', '/api/api-keys', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($expectedResponseData, true);
    }

    #[Test]
    #[TestDox('Zwraca ustawienia należące do użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'settingsData')]
    public function itReturnsUserSettings(EntityTestDataDto $settings): void
    {
        // Arrange
        $entityData = $settings->getEntityData();
        $fbId = $settings->getParameter('fbId');
        $expectedResponseData = [
            'autocomplete' => $entityData['autocomplete'],
            'ozaKey' => $entityData['ozaKey'],
            'userType' => $fbId === null ? 'standard' : 'facebook',
        ];
        $user = EntityFactory::createUser($settings->getUserEmail(), ['fbId' => $fbId]);
        $token = $this->getAccessToken($user);
        EntityFactory::createSettings($settings->getUserEmail(), $entityData);

        // Act
        $this->sendAuthorizedRequest('GET', '/api/settings', $token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($expectedResponseData);
    }

    #[Test]
    #[TestDox('Tworzy nowego użytkownika i zwraca 201, gdy dane są poprawne')]
    public function itStoresUserWhenValid(): void
    {
        // Arrange
        $content = [
            'email' => EntityFactory::USER_EMAIL_2,
            'password' => [
                'first' => 'password',
                'second' => 'password',
            ],
        ];

        // Act
        $this->client->request('POST', '/api/register', content: json_encode($content));

        // Prepare expected
        $createdUser = $this->getLastCreatedUser();

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertSame(EntityFactory::USER_EMAIL_2, $createdUser->getEmail());
        $this->assertNull($createdUser->getFbId());
    }

    #[Test]
    #[TestDox('Zmienia aktywność klucza API')]
    public function itSwitchKeyActivity(): void
    {
        // Arrange
        $key = str_repeat('a', 128);
        $apiKey = EntityFactory::createApiKey($this->user->getEmail(), ['key' => $key, 'active' => false]);

        // Act
        $this->sendAuthorizedRequest(
            'PATCH',
            sprintf('/api/api-keys/%s', $apiKey->getId()),
            $this->token,
        );

        // Prepare expected
        $apiKeyData = [
            'active' => true,
            'id' => $apiKey->getId(),
            'key' => $key,
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($apiKeyData);
    }

    #[Test]
    #[TestDox('Włącza lub wyłącza autouzupełnianie')]
    public function itTogglesAutocomplete(): void
    {
        // Arrange
        $settings = EntityFactory::createSettings($this->user->getEmail(), ['autocomplete' => false]);

        // Act
        $this->sendAuthorizedRequest('PATCH', '/api/settings/switch-autocomplete', $this->token);

        // Prepare expected
        $settingsData = [
            'autocomplete' => true,
            'ozaKey' => $settings->getOzaKey(),
            'userType' => $settings->getUser()->getFbId() === null ? 'standard' : 'facebook',
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($settingsData);
    }

    #[Test]
    #[TestDox('Aktualizuje klucz OZA gdy dane są poprawne')]
    public function itUpdatesOzaKeyWhenValid(): void
    {
        // Arrange
        $token = $this->initUser(EntityFactory::USER_EMAIL_2, [], ['ozaKey' => 'old', 'autocomplete' => true]);

        // Act
        $this->sendAuthorizedJsonRequest(
            'PATCH',
            '/api/settings/change-oza-key',
            ['key' => 'new-key'],
            $token,
        );

        // Prepare expected
        $settingsData = [
            'autocomplete' => true,
            'ozaKey' => 'new-key',
            'userType' => 'standard',
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($settingsData);
    }

    private function getLastCreatedApiKey(): ApiKeyEntity
    {
        return $this->apiKeyRepository->findOneBy([], ['id' => 'DESC']);
    }

    private function getLastCreatedUser(): User
    {
        return $this->userRepository->findOneBy([], ['id' => 'DESC']);
    }

    private function initUser(string $email, array $userData = [], array $settingsData = []): string
    {
        $this->user = EntityFactory::createUser($email, $userData);
        $token = $this->getAccessToken($this->user);
        EntityFactory::createSettings($this->user->getEmail(), $settingsData);

        return $token;
    }
}
