<?php

namespace App\Tests\Unit\Service\Entity;

use App\Dto\Request\OzaKey;
use App\Dto\Request\Password;
use App\Entity\Settings;
use App\Entity\User;
use App\Service\Entity\IngredientService;
use App\Service\Entity\SettingsService;
use App\Service\OzaSuppliesService;
use App\Service\UserService;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\ChangePasswordValidation;
use App\Validation\OzaKeyValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(SettingsService::class)]
#[CoversClass(OzaKey::class)]
#[CoversClass(Password::class)]
class SettingsServiceTest extends BaseTestCase
{
    private Settings $settings;
    private SettingsService $settingsService;
    private User $user;

    protected function setUp(): void
    {
        $this->settings = EntityFactory::getSimpleSettings();
        $this->user = $this->getMockBuilder(User::class)
            ->onlyMethods(['getSettings'])
            ->getMock();
        $this->user->method('getSettings')->willReturn($this->settings);
    }

    #[Test]
    #[TestDox('Zmienia klucz OZA i czyści powiązania, gdy pobieranie zapasów się nie powiedzie')]
    public function itChangesOzaKeyAndClearsSuppliesOnFail(): void
    {
        // Arrange
        $this->settings->setOzaKey('old-key');
        $this->user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $this->settings),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$this->settings]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $ozaKeyValidation = $this->getMock(
            OzaKeyValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new OzaKey('new-key'), $this->once()),
        );
        $ingredient = EntityFactory::getSimpleIngredient();
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedVoidMethod('clearOzaKeys', $this->once()),
            new AllowedMethod('getFirstIngredientWithOza', $ingredient, $this->once()),
        );
        $ozaSuppliesService = $this->getMock(
            OzaSuppliesService::class,
            new AllowedMethod('downloadSupplies', false, $this->once()),
            new AllowedVoidMethod('setKey', $this->once()),
        );

        // Act
        $result = $this->settingsService->changeOzaKey($ozaKeyValidation, $ingredientService, $ozaSuppliesService);

        // Assert
        $this->assertTrue($result);
        $this->assertSame('new-key', $this->settings->getOzaKey());
    }

    #[Test]
    #[TestDox('Zmienia hasło, gdy przesłane dane są poprawne')]
    public function itChangesPasswordWhenValid(): void
    {
        // Arrange
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$this->user]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $changePasswordModel = $this->getMock(
            Password::class,
            new AllowedMethod('getPassword', 'password-hash', $this->once()),
        );
        $changePasswordValidation = $this->getMock(
            ChangePasswordValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $changePasswordModel, $this->once()),
        );

        // Act
        $result = $this->settingsService->changePassword($changePasswordValidation);

        // Assert
        $this->assertTrue($result);
        $this->assertSame('password-hash', $this->user->getPassword());
    }

    #[Test]
    #[TestDox('Nie zmienia hasła, gdy przesłane dane są błędne')]
    public function itDoesNotChangePasswordIfValidationFails(): void
    {
        // Arrange
        $this->init();
        $changePasswordValidation = $this->getMock(
            ChangePasswordValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $result = $this->settingsService->changePassword($changePasswordValidation);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Zmienia klucz OZA bez czyszczenia powiązanych zapasów')]
    public function itKeepsSuppliesWhenOzaKeyChanges(): void
    {
        // Arrange
        $this->settings->setOzaKey('old-key');
        $this->user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $this->settings),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$this->settings]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $ozaKeyValidation = $this->getMock(
            OzaKeyValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new OzaKey('new-key'), $this->once()),
        );
        $ingredient = EntityFactory::getSimpleIngredient();
        $ingredient->setOzaId(1);
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedMethod('getFirstIngredientWithOza', $ingredient, $this->once()),
        );
        $ozaSuppliesService = $this->getMock(
            OzaSuppliesService::class,
            new AllowedMethod('downloadSupplies', true, $this->once()),
            new AllowedVoidMethod('setKey', $this->once()),
            new AllowedMethod('getSupplies', [EntityFactory::getSimpleOzaSupply()], $this->once()),
        );

        // Act
        $result = $this->settingsService->changeOzaKey($ozaKeyValidation, $ingredientService, $ozaSuppliesService);

        // Assert
        $this->assertTrue($result);
        $this->assertSame('new-key', $this->settings->getOzaKey());
    }

    #[Test]
    #[TestDox('Nie zmienia klucza OZA, gdy przesłano błędne dane')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $this->init();
        $ozaKeyValidation = $this->getMock(
            OzaKeyValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );
        $ingredientService = $this->getMock(IngredientService::class);
        $ozaSuppliesService = $this->getMock(OzaSuppliesService::class);

        // Act
        $result = $this->settingsService->changeOzaKey($ozaKeyValidation, $ingredientService, $ozaSuppliesService);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Resetuje klucz OZA i czyści powiązania w składnikach')]
    public function itResetOzaKey(): void
    {
        // Arrange
        $this->settings->setOzaKey('oza-key');
        $this->user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $this->settings),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$this->settings]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $ozaKeyValidation = $this->getMock(
            OzaKeyValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new OzaKey(null), $this->once()),
        );
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedVoidMethod('clearOzaKeys', $this->once()),
        );
        $ozaSuppliesService = $this->getMock(OzaSuppliesService::class);

        // Act
        $result = $this->settingsService->changeOzaKey($ozaKeyValidation, $ingredientService, $ozaSuppliesService);

        // Assert
        $this->assertTrue($result);
        $this->assertNull($this->settings->getOzaKey());
    }

    #[Test]
    #[TestDox('Find zwraca null (nadpisanie metody z EntityService)')]
    public function itReturnsNullFromFind(): void
    {
        // Arrange
        $this->init();

        // Assert
        $this->assertNull($this->settingsService->find(1));
    }

    #[Test]
    #[TestDox('Zwraca ustawienia użytkownika')]
    public function itReturnsSettings(): void
    {
        // Arrange
        $this->init();

        // Assert
        $this->assertSame($this->settings, $this->settingsService->getSettings());
    }

    #[Test]
    #[TestDox('Ustawia nowy klucz Oza')]
    public function itSetsNewOzaKey(): void
    {
        // Arrange
        $this->settings->setOzaKey('old-key');
        $this->user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $this->settings),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$this->settings]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $ozaKeyValidation = $this->getMock(
            OzaKeyValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new OzaKey('new-key'), $this->once()),
        );
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedMethod('getFirstIngredientWithOza', null, $this->once()),
        );
        $ozaSuppliesService = $this->getMock(
            OzaSuppliesService::class,
            new AllowedVoidMethod('setKey', $this->once()),
        );

        // Act
        $result = $this->settingsService->changeOzaKey($ozaKeyValidation, $ingredientService, $ozaSuppliesService);

        // Assert
        $this->assertTrue($result);
        $this->assertSame('new-key', $this->settings->getOzaKey());
    }

    #[Test]
    #[TestDox('Przełącza autouzupełnianie i zapisuje ustawienia')]
    #[DataProviderExternal(CommonDataProvider::class, 'oppositeBoolValues')]
    public function itTogglesAutocomplete(bool $prev, bool $expected): void
    {
        // Arrange
        $this->settings->setAutocomplete($prev);
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$this->settings]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);

        // Act
        $this->settingsService->switchAutocomplete();

        // Assert
        $this->assertSame($expected, $this->settings->getAutocomplete());
    }

    #[Test]
    #[TestDox('Zmienia klucz OZA i pobiera zapasy, gdy przesłano poprawne dane')]
    public function itUpdatesOzaKeyAndLoadsSupplies(): void
    {
        // Arrange
        $this->settings->setOzaKey('old-key');
        $this->user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $this->settings),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$this->settings]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $ozaKeyValidation = $this->getMock(
            OzaKeyValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new OzaKey('new-key'), $this->once()),
        );
        $ingredient = EntityFactory::getSimpleIngredient();
        $ingredient->setOzaId(1);
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedVoidMethod('clearOzaKeys', $this->once()),
            new AllowedMethod('getFirstIngredientWithOza', $ingredient, $this->once()),
        );
        $ozaSuppliesService = $this->getMock(
            OzaSuppliesService::class,
            new AllowedMethod('downloadSupplies', true, $this->once()),
            new AllowedVoidMethod('setKey', $this->once()),
            new AllowedMethod('getSupplies', [EntityFactory::getSimpleOzaSupply(5)], $this->once()),
        );

        // Act
        $result = $this->settingsService->changeOzaKey($ozaKeyValidation, $ingredientService, $ozaSuppliesService);

        // Assert
        $this->assertTrue($result);
        $this->assertSame('new-key', $this->settings->getOzaKey());
    }

    private function init(?EntityManagerInterface $entityManager = null): void
    {
        $this->settingsService = new SettingsService(
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $this->getMock(UserService::class, new AllowedMethod('getUser', $this->user)),
        );
    }
}
