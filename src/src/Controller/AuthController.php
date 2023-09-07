<?php

namespace App\Controller;

use App\Form\FbLoginForm;
use App\Service\Auth\FBAuthenticator;
use App\Service\Auth\TokensService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends BaseController
{
    public const URL = 'url';
    public const AUTH_TOKEN = 'authToken';

    public function getRedirectUrl(FBAuthenticator $authenticator): Response
    {
        return new JsonResponse([
            self::URL => $authenticator->getRedirectUrl()
        ]);
    }

    public function login(Request $request, FBAuthenticator $authenticator, TokensService $tokensService): Response
    {
        $form = $this->createForm(FbLoginForm::class);
        $form->handleRequest($request);
        if (!($form->isSubmitted() && $form->isValid())) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }
        $authToken = $form->getData()[self::AUTH_TOKEN];
        if (!$authenticator->getUserInfo($authToken)) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }
        $authenticator->userExists() ? $authenticator->setUser() : $authenticator->createUser();

        return new JsonResponse($tokensService->getTokens($authenticator->getUser()));
    }
}
