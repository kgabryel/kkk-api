<?php

namespace App\Tests\Functional;

use App\Controller\ResetPasswordController;
use App\Repository\ResetPasswordRequestRepository;
use App\Tests\DataProvider\ControllerDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Large]
#[CoversClass(ResetPasswordController::class)]
class ResetPasswordControllerTest extends BaseFunctionalTestCase
{
    use MailerAssertionsTrait;

    private ResetPasswordRequestRepository $resetPasswordRequestRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetPasswordRequestRepository = self::getContainer()->get(ResetPasswordRequestRepository::class);
    }

    #[Test]
    #[TestDox('Zmienia hasło, gdy token i dane są poprawne')]
    public function itChangesPasswordSuccessfully(): void
    {
        // Arrange
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->user = EntityFactory::createUser(
            EntityFactory::USER_EMAIL_2,
            ['password' => 'old-password', 'fbId' => null],
        );
        $token = self::createResetPasswordRequest(EntityFactory::USER_EMAIL_2);

        // Act
        $this->client->request(
            'POST',
            sprintf('/api/change-password/%s', $token->getToken()),
            content: json_encode([
                'newPassword' => [
                    'first' => 'new-password',
                    'second' => 'new-password',
                ],
            ]),
        );

        // Refresh entities from DB
        $this->entityManager->refresh($this->user);
        $after = $this->resetPasswordRequestRepository->findOneBy(
            ['selector' => substr($token->getToken(), 0, 20)],
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertTrue($passwordHasher->isPasswordValid($this->user, 'new-password'));
        $this->assertNull($after);
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy token jest nieprawidłowy')]
    public function itRejectsWhenTokenIsInvalid(): void
    {
        // Act
        $this->client->request('POST', '/api/change-password/abc');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    #[TestDox('Zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $token = self::createResetPasswordRequest($this->user->getEmail());

        // Act
        $this->client->request('POST', sprintf('/api/change-password/%s', $token->getToken()));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Zwraca 400, gdy brakuje tokena')]
    public function itReturnsBadRequestWhenTokenIsMissing(): void
    {
        // Act
        $this->client->request('POST', '/api/reset-password');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy token jest nieprawidłowy')]
    #[DataProviderExternal(ControllerDataProvider::class, 'resetPasswordInvalidTokenCases')]
    public function itReturnsForbiddenForInvalidToken(?int $lifetime, string $suffixReplace, ?string $toCheck): void
    {
        // Arrange
        $token = self::createResetPasswordRequest($this->user->getEmail(), $lifetime);
        $publicToken = $token->getToken();
        if ($suffixReplace !== '') {
            $publicToken = substr_replace($publicToken, $suffixReplace, -strlen($suffixReplace));
        }

        // Act
        $this->client->request('GET', sprintf('/api/check-token/%s', $toCheck ?? $publicToken));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    #[TestDox('Potwierdza ważność tokenu - zwraca 204')]
    public function itReturnsNoContentForValidToken(): void
    {
        // Arrange
        $token = self::createResetPasswordRequest($this->user->getEmail());

        // Act
        $this->client->request('GET', sprintf('/api/check-token/%s', $token->getToken()));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    #[Test]
    #[TestDox('Zwraca 204 i nie wysyła e-maila, gdy użytkownik nie istnieje')]
    #[DataProviderExternal(ControllerDataProvider::class, 'resetPasswordUserNotFoundCases')]
    public function itReturnsNoContentWhenUserNotFound(string $userEmail, ?int $fbId, string $requestEmail): void
    {
        // Arrange
        EntityFactory::createUser($userEmail, ['fbId' => $fbId]);

        // Act
        $this->client->request(
            'POST',
            '/api/reset-password',
            content: json_encode(['email' => $requestEmail]),
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertEmailCount(0);
    }

    #[Test]
    #[TestDox('Wysyła e-mail resetujący i zwraca 204')]
    public function itSendsResetEmailAndReturnsNoContent(): void
    {
        // Arrange
        $this->user = EntityFactory::createUser(EntityFactory::USER_EMAIL_2, ['fbId' => null]);

        // Act
        $this->client->request(
            'POST',
            '/api/reset-password',
            content: json_encode(['email' => $this->user->getEmail()]),
        );

        // Prepare expected
        /** @var TemplatedEmail $email */
        $email = self::getMailerMessage(0);
        $selector = $this->getLastCreateResetPasswordRequest();

        // Assert
        self::assertEmailCount(1);
        self::assertSame($this->user->getEmail(), $email->getTo()[0]->getAddress());
        self::assertEmailHtmlBodyContains($email, $selector);
    }

    private function getLastCreateResetPasswordRequest(): string
    {
        return static::getContainer()->get(Connection::class)->fetchOne(
            'SELECT selector FROM reset_password_request ORDER BY id DESC LIMIT 1',
        );
    }
}
