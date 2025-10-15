<?php

namespace App\Factory;

use App\Repository\UserRepository;
use App\Service\Auth\FBAuthenticator;
use App\Service\Auth\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Facebook;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FBAuthenticatorFactory
{
    private EntityManagerInterface $manager;
    private ParameterBagInterface $parameterBag;
    private RegistrationService $registrationService;
    private UserRepository $userRepository;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $manager,
        ParameterBagInterface $parameterBag,
        RegistrationService $registrationService,
    ) {
        $this->userRepository = $userRepository;
        $this->manager = $manager;
        $this->parameterBag = $parameterBag;
        $this->registrationService = $registrationService;
    }

    public function create(): FBAuthenticator
    {
        $this->checkParamsPresence();

        $config = [
            'clientId' => $this->parameterBag->get('FB_ID'),
            'clientSecret' => $this->parameterBag->get('FB_SECRET'),
            'graphApiVersion' => 'v2.10',
            'redirectUri' => $this->parameterBag->get('FB_REDIRECT'),
        ];

        $this->validateConfigValues($config);

        $facebook = new Facebook($config);

        return new FBAuthenticator($facebook, $this->userRepository, $this->manager, $this->registrationService);
    }

    private function checkParamsPresence(): void
    {
        if (!$this->parameterBag->has('FB_ID')) {
            throw new RuntimeException('FB_ID parameter is not set.');
        }

        if (!$this->parameterBag->has('FB_SECRET')) {
            throw new RuntimeException('FB_SECRET parameter is not set.');
        }

        if (!$this->parameterBag->has('FB_REDIRECT')) {
            throw new RuntimeException('FB_REDIRECT parameter is not set.');
        }
    }

    private function validateConfigValues(array $config): void
    {
        if (empty($config['clientId']) || !is_string($config['clientId'])) {
            throw new RuntimeException('FB_ID parameter is empty or invalid.');
        }

        if (empty($config['clientSecret']) || !is_string($config['clientSecret'])) {
            throw new RuntimeException('FB_SECRET parameter is empty or invalid.');
        }

        if (empty($config['redirectUri']) || !is_string($config['redirectUri'])) {
            throw new RuntimeException('FB_REDIRECT parameter is empty or invalid.');
        }
    }
}
