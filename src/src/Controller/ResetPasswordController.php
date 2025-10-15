<?php

namespace App\Controller;

use App\Service\Auth\ResetPasswordService;
use App\Validation\ResetPasswordRequestValidation;
use App\Validation\ResetPasswordValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    private ResetPasswordHelperInterface $resetPasswordHelper;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    public function changePassword(
        string $token,
        ResetPasswordService $resetPasswordService,
        EntityManagerInterface $entityManager,
        ResetPasswordValidation $resetPasswordValidation,
    ): Response {
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            return new Response(status: Response::HTTP_FORBIDDEN);
        }

        if (!$resetPasswordValidation->validate()->passed()) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $resetPasswordService->changePassword(
            $token,
            $entityManager,
            $resetPasswordValidation->getDto($user)->getPassword(),
        );

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    public function checkToken(string $token): Response
    {
        try {
            $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $exception) {
            return new Response(status: Response::HTTP_FORBIDDEN);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    public function sendEmail(
        ResetPasswordService $resetPasswordService,
        ResetPasswordRequestValidation $resetPasswordRequestValidation,
        ParameterBagInterface $parameterBag,
    ): Response {
        if (!$resetPasswordRequestValidation->validate()->passed()) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $resetPasswordService->sendResetEmail($parameterBag, $resetPasswordRequestValidation->getDto()->getEmail());

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
