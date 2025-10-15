<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\PathService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordService
{
    private MailerInterface $mailer;
    private PathService $pathService;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private UserRepository $userRepository;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer,
        UserRepository $userRepository,
        PathService $pathService,
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->pathService = $pathService;
    }

    public function changePassword(string $token, EntityManagerInterface $entityManager, string $newPassword): void
    {
        /** @var User $user */
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        $this->resetPasswordHelper->removeResetRequest($token);
        $user->setPassword($newPassword);
        $entityManager->persist($user);
        $entityManager->flush();
    }

    public function sendResetEmail(ParameterBagInterface $parameterBag, string $userEmail): void
    {
        if (!$parameterBag->has('EMAIL_ADDRESS')) {
            throw new RuntimeException('EMAIL_ADDRESS parameter is not set.');
        }

        $emailAddress = $parameterBag->get('EMAIL_ADDRESS');
        if (empty($emailAddress) || !is_string($emailAddress) || !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('EMAIL_ADDRESS parameter is empty or invalid.');
        }

        $user = $this->userRepository->findOneBy([
            'email' => $userEmail,
            'fbId' => null,
        ]);
        if ($user === null) {
            return;
        }

        $resetToken = $this->resetPasswordHelper->generateResetToken($user);

        $email = new TemplatedEmail()
            ->from(Address::create(sprintf('KKK powiadomienia <%s>', $emailAddress)))
            ->to($user->getEmail())
            ->subject('KKK - reset hasła')
            ->htmlTemplate('resetPassword.html.twig')
            ->context([
                'banner' => $this->pathService->getPathToFront('assets/logo.png'),
                'url' => $this->pathService->getPathToFront(sprintf('change-password/%s', $resetToken->getToken())),
            ]);
        $this->mailer->send($email);
    }
}
