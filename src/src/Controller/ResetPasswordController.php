<?php

namespace App\Controller;

use App\Form\ResetPasswordForm;
use App\Form\ResetPasswordRequestForm;
use App\Service\Auth\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    private ResetPasswordHelperInterface $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    public function sendEmail(ResetPasswordService $resetPasswordService, Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestForm::class);
        $resetPasswordService->setForm($form);
        if ($resetPasswordService->checkForm($request)) {
            $resetPasswordService->sendResetEmail();

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return new Response(null, Response::HTTP_BAD_REQUEST);
    }

    public function checkToken(string $token): Response
    {
        try {
            $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    public function changePassword(
        string $token,
        ResetPasswordService $resetPasswordService,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        try {
            $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            return new Response(null, Response::HTTP_FORBIDDEN);
        }
        $form = $this->createForm(ResetPasswordForm::class);
        $resetPasswordService->setForm($form);
        if ($resetPasswordService->checkForm($request)) {
            $resetPasswordService->changePassword($token, $passwordEncoder, $entityManager);

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return new Response(null, Response::HTTP_BAD_REQUEST);
    }
}
