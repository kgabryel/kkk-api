<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Entity\SettingsService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\OAuth2\Client\Provider\Facebook;
use Symfony\Component\String\ByteString;

class FBAuthenticator
{
    public const AUTHORIZATION_CODE = 'authorization_code';
    public const CODE = 'code';
    public const FB_ID = 'fbId';
    public const ID = 'id';
    public const EMAIL_SUFFIX = '@fb.com';
    private Facebook $facebook;
    private array $userInfo;
    private User $user;
    private UserRepository $userRepository;
    private EntityManagerInterface $manager;

    public function __construct(Facebook $facebook, UserRepository $userRepository, EntityManagerInterface $manager)
    {
        $this->userInfo = [];
        $this->facebook = $facebook;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    public function getUserInfo(string $authToken): bool
    {
        try {
            $accessToken = $this->facebook->getAccessToken(
                self::AUTHORIZATION_CODE,
                [self::CODE => $authToken]
            );
            $this->userInfo = $this->facebook->getResourceOwner($accessToken)->toArray();
        } catch (Exception) {
            return false;
        }

        return true;
    }

    public function userExists(): bool
    {
        return $this->userRepository->findOneBy([
                self::FB_ID => $this->userInfo[self::ID]
            ]) !== null;
    }

    public function createUser(): self
    {
        $this->user = new User();
        $this->user->setEmail(sprintf('%s%s', $this->userInfo[self::ID], self::EMAIL_SUFFIX));
        $this->user->setFbId($this->userInfo[self::ID]);
        $this->user->setPassword(ByteString::fromRandom(30)->toString());
        $settings = SettingsService::get($this->user);
        $this->manager->persist($settings);
        $this->manager->persist($this->user);
        $this->manager->flush();

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(): self
    {
        $this->user = $this->userRepository->findOneBy([
            self::FB_ID => $this->userInfo[self::ID]
        ]);

        return $this;
    }

    public function getRedirectUrl(): string
    {
        return $this->facebook->getAuthorizationUrl();
    }
}
