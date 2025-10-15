<?php

namespace App\Tests\Unit\Controller;

use App\Controller\ResetPasswordController;
use App\Dto\Request\Password;
use App\Dto\Request\ResetPasswordRequest;
use App\Entity\User;
use App\Service\Auth\ResetPasswordService;
use App\Tests\Helper\AllowedMethod\AllowedExceptionMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\ResetPasswordRequestValidation;
use App\Validation\ResetPasswordValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Small]
#[CoversClass(ResetPasswordController::class)]
#[CoversClass(ResetPasswordRequest::class)]
class ResetPasswordControllerTest extends BaseTestCase
{
    private ResetPasswordController $controller;
    private ParameterBagInterface $parameterBag;
    private User $user;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->init($this->getMock(ResetPasswordHelperInterface::class));
        $this->parameterBag = $this->createStub(ParameterBagInterface::class);
    }

    #[Test]
    #[TestDox('Nie wysyła e-maila i zwraca 400, gdy dane są niepoprawne')]
    public function itDoesNotSendEmailWhenValidationFails(): void
    {
        // Arrange
        $resetPasswordService = $this->getMock(ResetPasswordService::class);
        $resetPasswordValidation = $this->getMock(
            ResetPasswordRequestValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $response = $this->controller->sendEmail($resetPasswordService, $resetPasswordValidation, $this->parameterBag);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie resetuje hasła i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnChangePassword(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                ResetPasswordHelperInterface::class,
                new AllowedMethod('validateTokenAndFetchUser', $this->user, $this->once()),
            ),
        );
        $entityManager = $this->getMock(EntityManagerInterface::class);
        $resetPasswordValidation = $this->getMock(
            ResetPasswordValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );
        $resetPasswordService = $this->getMock(ResetPasswordService::class);

        // Act
        $response = $this->controller->changePassword(
            'token',
            $resetPasswordService,
            $entityManager,
            $resetPasswordValidation,
        );

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie zmienia hasła i zwraca 403, gdy validateTokenAndFetchUser rzuci wyjątek')]
    public function itRejectsPasswordResetWhenTokenIsInvalid(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                ResetPasswordHelperInterface::class,
                new AllowedExceptionMethod(
                    'validateTokenAndFetchUser',
                    $this->once(),
                    $this->createStub(ResetPasswordExceptionInterface::class),
                ),
            ),
        );
        $entityManager = $this->getMock(EntityManagerInterface::class);
        $resetPasswordValidation = $this->getMock(ResetPasswordValidation::class);
        $resetPasswordService = $this->getMock(ResetPasswordService::class);

        // Act
        $response = $this->controller->changePassword(
            'token',
            $resetPasswordService,
            $entityManager,
            $resetPasswordValidation,
        );

        // Assert
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Resetuje hasło i zwraca 204, gdy dane są poprawne')]
    public function itResetPasswordWhenValid(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                ResetPasswordHelperInterface::class,
                new AllowedMethod('validateTokenAndFetchUser', $this->user, $this->once()),
            ),
        );
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $resetPasswordValidation = $this->getMock(
            ResetPasswordValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $this->getMock(
                Password::class,
                new AllowedMethod('getPassword', 'password', $this->once()),
            ), $this->once()),
        );
        $resetPasswordService = $this->getMock(
            ResetPasswordService::class,
            new AllowedVoidMethod('changePassword', $this->once(), ['token', $entityManager, 'password']),
        );

        // Act
        $response = $this->controller->changePassword(
            'token',
            $resetPasswordService,
            $entityManager,
            $resetPasswordValidation,
        );

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 204, gdy token jest poprawny (validateTokenAndFetchUser nie rzuca wyjątku)')]
    public function itReturnsNoContentWhenTokenIsValid(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                ResetPasswordHelperInterface::class,
                new AllowedMethod('validateTokenAndFetchUser', $this->user, $this->once()),
            ),
        );

        // Act
        $response = $this->controller->checkToken('token');

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy validateTokenAndFetchUser rzuci wyjątek przy sprawdzaniu tokena')]
    public function itReturnsUnauthorizedWhenTokenIsInvalid(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                ResetPasswordHelperInterface::class,
                new AllowedExceptionMethod(
                    'validateTokenAndFetchUser',
                    $this->once(),
                    $this->createStub(ResetPasswordExceptionInterface::class),
                ),
            ),
        );

        // Act
        $response = $this->controller->checkToken('token');

        // Assert
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Wysyła e-mail i zwraca 204, gdy dane są poprawne')]
    public function itSendEmailWhenValid(): void
    {
        // Arrange
        $resetPasswordService = $this->getMock(
            ResetPasswordService::class,
            new AllowedVoidMethod(
                'sendResetEmail',
                $this->once(),
                [$this->parameterBag, EntityFactory::USER_EMAIL],
            ),
        );
        $resetPasswordRequestValidation = $this->getMock(
            ResetPasswordRequestValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod(
                'getDto',
                new ResetPasswordRequest(EntityFactory::USER_EMAIL),
                $this->once(),
            ),
        );

        // Act
        $response = $this->controller->sendEmail(
            $resetPasswordService,
            $resetPasswordRequestValidation,
            $this->parameterBag,
        );

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    private function init(ResetPasswordHelperInterface $resetPasswordHelper): void
    {
        $this->controller = new ResetPasswordController($resetPasswordHelper);
    }
}
