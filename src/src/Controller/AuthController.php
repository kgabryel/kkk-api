<?php

namespace App\Controller;

use App\Service\Auth\FBAuthenticator;
use App\Service\Auth\TokensService;
use App\Validation\FbLoginValidation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends BaseController
{
    public const string URL = 'url';

    public function getRedirectUrl(FBAuthenticator $authenticator): Response
    {
        return new JsonResponse([
            self::URL => $authenticator->getRedirectUrl(),
        ]);
    }

    public function login(
        FBAuthenticator $authenticator,
        TokensService $tokensService,
        FbLoginValidation $fbLoginValidation,
    ): Response {
        if (!$fbLoginValidation->validate()->passed()) {
            return $this->getUnauthorizedResponse();
        }

        $userInfo = $authenticator->getUserInfo($fbLoginValidation->getDto()->getToken());
        if ($userInfo === false) {
            return $this->getUnauthorizedResponse();
        }

        if (!$authenticator->userExists($userInfo[FBAuthenticator::ID])) {
            $authenticator->createUser($userInfo);
        }

        return new JsonResponse($tokensService->getTokens($authenticator->getUser($userInfo[FBAuthenticator::ID])));
    }
}
