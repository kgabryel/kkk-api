<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\OAuth2\Client\Provider\Facebook;
use Symfony\Component\String\ByteString;

class FBAuthenticator
{
    public const string AUTHORIZATION_CODE = 'authorization_code';
    public const string CODE = 'code';
    public const string EMAIL_SUFFIX = '@fb.com';
    public const string FB_ID = 'fbId';
    public const string ID = 'id';
    private Facebook $facebook;
    private EntityManagerInterface $manager;
    private RegistrationService $registrationService;
    private UserRepository $userRepository;

    public function __construct(
        Facebook $facebook,
        UserRepository $userRepository,
        EntityManagerInterface $manager,
        RegistrationService $registrationService,
    ) {
        $this->facebook = $facebook;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
        $this->registrationService = $registrationService;
    }

    public function createUser(array $userInfo): User
    {
        $user = new User();
        $user->setEmail(sprintf('%s%s', $userInfo[self::ID], self::EMAIL_SUFFIX));
        $user->setFbId($userInfo[self::ID]);
        $user->setPassword(ByteString::fromRandom(30)->toString());
        $settings = $this->registrationService->getSettings($user);
        $this->manager->persist($settings);
        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    public function getRedirectUrl(): string
    {
        return $this->facebook->getAuthorizationUrl();
    }

    public function getUser(string $facebookId): User
    {
        return $this->userRepository->findOneBy([self::FB_ID => $facebookId]);
    }

    public function getUserInfo(string $authToken): false|array
    {
        try {
            $accessToken = $this->facebook->getAccessToken(self::AUTHORIZATION_CODE, [self::CODE => $authToken]);

            return $this->facebook->getResourceOwner($accessToken)->toArray();
        } catch (Exception) {
            return false;
        }
    }

    public function userExists(string $facebookId): bool
    {
        return $this->userRepository->findOneBy([self::FB_ID => $facebookId]) !== null;
    }
}
