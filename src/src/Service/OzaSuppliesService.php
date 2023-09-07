<?php

namespace App\Service;

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OzaSuppliesService
{
    private const URL = '/api/supplies';
    private string $ozaUrl;
    private HttpClientInterface $client;
    private ?string $ozaKey;
    private array $supplies;
    private int $errorStatus;
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, HttpClientInterface $client)
    {
        $this->tokenStorage = $tokenStorage;
        $this->setKey();
        $this->client = $client;
        $this->ozaUrl = $_ENV['OZA_URL'];
    }

    public function setKey(): void
    {
        $this->ozaKey = $this->tokenStorage->getToken()->getUser()->getSettings()->getOzaKey();
    }

    public function downloadSupplies(): bool
    {
        if ($this->ozaKey === null) {
            $this->errorStatus = 401;

            return false;
        }
        try {
            $response = $this->client->request(
                Request::METHOD_GET,
                sprintf('%s%s', $this->ozaUrl, self::URL),
                [
                    'headers' => [
                        'X-AUTH-TOKEN' => $this->ozaKey,
                        'X-Requested-With' => 'XMLHttpRequest'
                    ]
                ]
            );
            $this->supplies = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);

            return true;
        } catch (ClientException $exception) {
            $this->errorStatus = $exception->getCode();
        }

        return false;
    }

    public function getErrorStatusCode(): int
    {
        return $this->errorStatus === 401 ? 403 : $this->errorStatus;
    }

    public function getSupplies(): array
    {
        return $this->supplies;
    }
}
