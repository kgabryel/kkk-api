<?php

namespace App\Service\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokensService
{
    private EntityManagerInterface $entityManager;
    private RefreshTokenGeneratorInterface $refreshTokenManager;
    private JWTTokenManagerInterface $tokenManager;

    public function __construct(
        JWTTokenManagerInterface $tokenManager,
        RefreshTokenGeneratorInterface $refreshTokenManager,
        EntityManagerInterface $entityManager,
    ) {
        $this->tokenManager = $tokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->entityManager = $entityManager;
    }

    public function getTokens(User $user): array
    {
        $refreshToken = $this->refreshTokenManager->createForUserWithTtl($user, 2592000);
        $refreshToken->setUsername($user->getUserIdentifier());
        $refreshToken->setRefreshToken();
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return [
            'refresh_token' => $refreshToken->getRefreshToken(),
            'token' => $this->tokenManager->create($user),
        ];
    }
}
