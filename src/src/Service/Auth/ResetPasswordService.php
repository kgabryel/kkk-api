<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\PathUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordService
{
    private FormInterface $form;
    private ResetPasswordHelperInterface $resetPasswordHelper;
    private MailerInterface $mailer;
    private UserRepository $userRepository;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer,
        UserRepository $userRepository
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }

    public function setForm(FormInterface $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function checkForm(Request $request): bool
    {
        $this->form->handleRequest($request);

        return $this->form->isSubmitted() && $this->form->isValid();
    }

    public function sendResetEmail(): void
    {
        $email = $this->form->getData()->getEmail();
        $user = $this->userRepository->findOneBy([
            'email' => $email,
            'fbId' => null
        ]);
        if ($user === null) {
            return;
        }
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        $email = (new TemplatedEmail())
            ->from(Address::create('KKK powiadomienia <drink.notifications@gmail.com>'))
            ->to($user->getEmail())
            ->subject('KKK - reset hasła')
            ->htmlTemplate('resetPassword.html.twig')
            ->context([
                'url' => PathUtils::getPathToFront(sprintf('change-password/%s', $resetToken->getToken())),
                'banner' => PathUtils::getPathToFront('assets/logo.png')
            ]);

        $this->mailer->send($email);
    }

    public function changePassword(
        string $token,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ): void {
        /** @var User $user */
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        $this->resetPasswordHelper->removeResetRequest($token);
        $encodedPassword = $passwordEncoder->encodePassword($user, $this->form->getData()->getNewPassword());

        $user->setPassword($encodedPassword);
        $entityManager->flush();
    }
}
