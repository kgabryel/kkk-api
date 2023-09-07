<?php

namespace App\Service\Auth;

use App\Entity\User;
use DateTime;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokensService
{
    private JWTTokenManagerInterface $JWTTokenManager;
    private RefreshTokenManagerInterface $refreshTokenManager;

    public function __construct(
        JWTTokenManagerInterface $JWTTokenManager,
        RefreshTokenManagerInterface $refreshTokenManager
    ) {
        $this->JWTTokenManager = $JWTTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    public function getTokens(User $user): array
    {
        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($user->getUsername());
        $datetime = new DateTime();
        $datetime->modify('+2592000 seconds');
        $refreshToken->setValid($datetime);
        $refreshToken->setRefreshToken();
        $this->refreshTokenManager->save($refreshToken);

        return [
            'token' => $this->JWTTokenManager->create($user),
            'refresh_token' => $refreshToken->getRefreshToken()
        ];
    }
}
