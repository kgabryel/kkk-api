<?php

namespace App\Tests\Unit\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\ResetPasswordService;
use App\Service\PathService;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Small]
#[CoversClass(ResetPasswordService::class)]
class ResetPasswordServiceTest extends BaseTestCase
{
    private ResetPasswordService $resetPasswordService;

    #[Test]
    #[TestDox('Zmienia hasło')]
    public function itChangesPassword(): void
    {
        // Arrange
        $user = $this->getMock(
            User::class,
            new AllowedVoidMethod('setPassword', $this->once(), ['new-password']),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('flush', $this->once()),
            new AllowedVoidMethod('persist', $this->once(), [$user]),
        );
        $resetHelper = $this->getMock(
            ResetPasswordHelperInterface::class,
            new AllowedMethod('validateTokenAndFetchUser', $user, $this->once(), ['dummy-token']),
            new AllowedVoidMethod('removeResetRequest', $this->once(), ['dummy-token']),
        );
        $this->init($resetHelper);

        // Act
        $this->resetPasswordService->changePassword('dummy-token', $entityManager, 'new-password');
    }

    #[Test]
    #[TestDox('Nie wysyła e-maila i zwraca false, gdy użytkownik nie istnieje')]
    public function itReturnsFalseWhenUserNotFound(): void
    {
        // Arrange
        $userRepository = $this->initUserRepository();
        $this->init(userRepository: $userRepository);
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, parameters: ['EMAIL_ADDRESS']),
            new AllowedMethod('get', EntityFactory::USER_EMAIL),
        );

        // Act
        $this->resetPasswordService->sendResetEmail($parameterBag, EntityFactory::USER_EMAIL_2);
    }

    #[Test]
    #[TestDox('Wysyła e-mail resetu, gdy użytkownik istnieje')]
    public function itSendsEmail(): void
    {
        // Arrange
        $emailData = null;
        $user = EntityFactory::getSimpleUser();
        $user->setEmail(EntityFactory::USER_EMAIL_2);
        $mailer = $this->getMock(
            MailerInterface::class,
            new AllowedVoidMethod(
                'send',
                $this->once(),
                [$this->callback($this->dataAssigner($emailData))],
            ),
        );
        $pathService = $this->getMock(
            PathService::class,
            new AllowedCallbackMethod(
                'getPathToFront',
                static fn (string $arg): string => 'https://frontend.test/' . $arg,
                $this->exactly(2),
            ),
        );
        $this->init(
            $this->createStub(ResetPasswordHelperInterface::class),
            $mailer,
            $this->initUserRepository($user),
            $pathService,
        );
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, parameters: ['EMAIL_ADDRESS']),
            new AllowedMethod('get', EntityFactory::USER_EMAIL),
        );

        // Act
        $this->resetPasswordService->sendResetEmail($parameterBag, EntityFactory::USER_EMAIL_2);

        // Prepare expected
        $context = $emailData->getContext();

        // Assert
        $this->assertSame('KKK - reset hasła', $emailData->getSubject());
        $this->assertSame(EntityFactory::USER_EMAIL_2, $emailData->getTo()[0]->getAddress());
        $this->assertSame('resetPassword.html.twig', $emailData->getHtmlTemplate());
        $this->assertArrayHasKey('url', $context);
        $this->assertArrayHasKey('banner', $context);
        $this->assertStringContainsString('change-password/', $context['url']);
        $this->assertStringContainsString('assets/logo.png', $context['banner']);
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy EMAIL_ADDRESS jest nieprawidłowy')]
    public function itThrowsExceptionIfEmailAddressInvalid(): void
    {
        // Arrange
        $this->init();
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, parameters: ['EMAIL_ADDRESS']),
            new AllowedMethod('get', 'not-an-email'),
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EMAIL_ADDRESS parameter is empty or invalid.');

        // Act
        $this->resetPasswordService->sendResetEmail($parameterBag, EntityFactory::USER_EMAIL);
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy EMAIL_ADDRESS nie jest ustawione')]
    public function itThrowsExceptionIfEmailAddressMissing(): void
    {
        // Arrange
        $this->init();
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', false, parameters: ['EMAIL_ADDRESS']),
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('EMAIL_ADDRESS parameter is not set.');

        // Act
        $this->resetPasswordService->sendResetEmail($parameterBag, EntityFactory::USER_EMAIL);
    }

    private function dataAssigner(?TemplatedEmail &$emailData): callable
    {
        return static function (TemplatedEmail $email) use (&$emailData): true {
            $emailData = $email;

            return true;
        };
    }

    private function init(
        ?ResetPasswordHelperInterface $resetPasswordHelper = null,
        ?MailerInterface $mailer = null,
        ?UserRepository $userRepository = null,
        ?PathService $pathService = null
    ): void {
        $this->resetPasswordService = new ResetPasswordService(
            $resetPasswordHelper ?? $this->getMock(ResetPasswordHelperInterface::class),
            $mailer ?? $this->getMock(MailerInterface::class),
            $userRepository ?? $this->getMock(UserRepository::class),
            $pathService ?? $this->getMock(PathService::class),
        );
    }

    private function initUserRepository(?User $user = null): UserRepository
    {
        return $this->getMock(
            UserRepository::class,
            new AllowedMethod('findOneBy', $user, $this->once()),
        );
    }
}
