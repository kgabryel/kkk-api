<?php

namespace App\Tests\Unit\Controller;

use App\Controller\UserController;
use App\Dto\Entity\List\ApiKeyList;
use App\Dto\Entity\Settings;
use App\Dto\Request\User;
use App\Entity\ApiKey;
use App\Entity\Settings as SettingsEntity;
use App\Entity\User as UserEntity;
use App\Factory\DtoFactoryDispatcher;
use App\Factory\Entity\ApiKeyFactory;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Service\Auth\RegistrationService;
use App\Service\Entity\ApiKeyService;
use App\Service\Entity\IngredientService;
use App\Service\Entity\SettingsService;
use App\Service\OzaSuppliesService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\ChangePasswordValidation;
use App\Validation\OzaKeyValidation;
use App\Validation\RegisterValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(UserController::class)]
class UserControllerTest extends BaseTestCase
{
    private ApiKey $apiKey;
    private UserController $controller;
    private IngredientService $ingredientService;
    private OzaKeyValidation $ozaKeyValidation;
    private OzaSuppliesService $ozaSuppliesService;
    private SettingsEntity $settings;
    private UserEntity $user;

    protected function setUp(): void
    {
        $this->apiKey = EntityFactory::getSimpleApiKey();
        $this->settings = EntityFactory::getSimpleSettings();
        $this->user = EntityFactory::getSimpleUser();
        $this->ingredientService = $this->createStub(IngredientService::class);
        $this->ozaSuppliesService = $this->createStub(OzaSuppliesService::class);
        $this->ozaKeyValidation = $this->createStub(OzaKeyValidation::class);
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('getMany', new ApiKeyList()),
            new AllowedMethod('get', $this->createStub(Settings::class)),
        );
        $userRepository = $this->getMock(UserRepository::class);
        $this->controller = $this->getMockBuilder(UserController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn($this->user);
    }

    #[Test]
    #[TestDox('Zmienia klucz OZA, gdy wszystko gra')]
    public function itChangesOzaKeySuccessfully(): void
    {
        // Arrange
        $settingsService = $this->getMock(
            SettingsService::class,
            new AllowedMethod('changeOzaKey', true, $this->once()),
            new AllowedMethod('getSettings', $this->settings, $this->once()),
        );

        // Act
        $response = $this->controller->changeOzaKey(
            $settingsService,
            $this->ingredientService,
            $this->ozaSuppliesService,
            $this->ozaKeyValidation,
        );

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zmienia hasło, gdy wszystko się zgadza')]
    public function itChangesPassword(): void
    {
        // Arrange
        $this->user->setFbId(null);
        $changePasswordValidation = $this->createStub(ChangePasswordValidation::class);
        $settingsService = $this->getMock(
            SettingsService::class,
            new AllowedMethod('changePassword', true, $this->once()),
        );

        // Act
        $response = $this->controller->changePassword($settingsService, $changePasswordValidation);

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Usuwa encję (ApiKey) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserApiKeyWhenAvailable(): void
    {
        // Arrange
        $apiKeyService = $this->getMock(
            ApiKeyService::class,
            new AllowedMethod('find', $this->apiKey, $this->once(), [1]),
            new AllowedVoidMethod('remove', $this->once(), [$this->apiKey]),
        );

        // Act
        $response = $this->controller->destroyKey(1, $apiKeyService);

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca błąd, gdy zmiana klucza OZA się nie powiedzie')]
    public function itFailsToChangeOzaKey(): void
    {
        // Arrange
        $settingsService = $this->getMock(
            SettingsService::class,
            new AllowedMethod('changeOzaKey', false, $this->once()),
        );

        // Act
        $response = $this->controller->changeOzaKey(
            $settingsService,
            $this->ingredientService,
            $this->ozaSuppliesService,
            $this->ozaKeyValidation,
        );

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Generowanie klucza kończy się błędem')]
    public function itFailsToGenerateKey(): void
    {
        // Arrange
        $apiKeyFactory = $this->getMock(
            ApiKeyFactory::class,
            new AllowedMethod('generate', null, $this->once()),
        );

        // Act
        $response = $this->controller->generateKey($apiKeyFactory);

        // Assert
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie pozwala na zmianę hasła użytkownikom z kontem Facebook')]
    public function itForbidsPasswordChangeForFbUser(): void
    {
        // Arrange
        $this->user->setFbId('id');
        $changePasswordValidation = $this->createStub(ChangePasswordValidation::class);
        $settingsService = $this->getMock(SettingsService::class);

        // Act
        $response = $this->controller->changePassword($settingsService, $changePasswordValidation);

        // Assert
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Generuje klucz, gdy wszystko działa')]
    public function itGeneratesKey(): void
    {
        // Arrange
        $apiKeyFactory = $this->getMock(
            ApiKeyFactory::class,
            new AllowedMethod('generate', $this->apiKey, $this->once()),
        );

        // Act
        $response = $this->controller->generateKey($apiKeyFactory);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Rejestruje użytkownika, gdy dane są poprawne')]
    public function itRegistersUser(): void
    {
        // Arrange
        $dto = $this->getMock(
            User::class,
            new AllowedMethod('getUser', $this->user, $this->once()),
        );
        $registerValidation = $this->getMock(
            RegisterValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $dto, $this->once()),
        );
        $registrationService = $this->getMock(
            RegistrationService::class,
            new AllowedVoidMethod('register', $this->once(), [$this->user]),
        );

        // Act
        $response = $this->controller->register($registerValidation, $registrationService);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie usuwa encji (ApiKey) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsDeleteApiKeyWhenUnavailable(): void
    {
        // Arrange
        $apiKeyService = $this->getMock(
            ApiKeyService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->destroyKey(1, $apiKeyService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie zmiany hasła i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnPasswordChange(): void
    {
        // Arrange
        $this->user->setFbId(null);
        $changePasswordValidation = $this->createStub(ChangePasswordValidation::class);
        $settingsService = $this->getMock(
            SettingsService::class,
            new AllowedMethod('changePassword', false, $this->once()),
        );

        // Act
        $response = $this->controller->changePassword($settingsService, $changePasswordValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie rejestracji i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnRegister(): void
    {
        // Arrange
        $registerValidation = $this->getMock(
            RegisterValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );
        $registrationService = $this->getMock(RegistrationService::class);

        // Act
        $response = $this->controller->register($registerValidation, $registrationService);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca przełączanie klucza, gdy nie istnieje')]
    public function itRejectsSwitchingNonexistentKey(): void
    {
        // Arrange
        $apiKeyService = $this->getMock(
            ApiKeyService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->switchKey(1, $apiKeyService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca listę kluczy API')]
    public function itReturnsApiKeys(): void
    {
        // Arrange
        $apiKeyRepository = $this->getMock(
            ApiKeyRepository::class,
            new AllowedMethod('findForUser', [], $this->once()),
        );

        // Act
        $response = $this->controller->getKeys($apiKeyRepository);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca ustawienia użytkownika')]
    public function itReturnsSettings(): void
    {
        // Arrange
        $settingsService = $this->getMock(
            SettingsService::class,
            new AllowedMethod('getSettings', $this->settings, $this->once()),
        );

        // Act
        $response = $this->controller->getSettings($settingsService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Przełącza klucz, gdy istnieje')]
    public function itSwitchesKey(): void
    {
        // Arrange
        $apiKeyService = $this->getMock(
            ApiKeyService::class,
            new AllowedMethod('find', $this->apiKey, $this->once(), [1]),
            new AllowedVoidMethod('switch', $this->once(), [$this->apiKey]),
        );

        // Act
        $response = $this->controller->switchKey(1, $apiKeyService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Włącza lub wyłącza autouzupełnianie')]
    public function itTogglesAutocomplete(): void
    {
        // Arrange
        $settingsService = $this->getMock(
            SettingsService::class,
            new AllowedVoidMethod('switchAutocomplete', $this->once()),
            new AllowedMethod('getSettings', $this->settings, $this->once()),
        );

        // Act
        $response = $this->controller->switchAutocomplete($settingsService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
