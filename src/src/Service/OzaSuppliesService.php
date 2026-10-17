<?php

namespace App\Service;

use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OzaSuppliesService
{
    private const URL = '/api/supplies';
    private HttpClientInterface $client;
    private int $errorStatus;
    private ?string $ozaKey;
    private string $ozaUrl;
    private array $supplies;
    private UserService $userService;

    public function __construct(
        UserService $userService,
        HttpClientInterface $client,
        ParameterBagInterface $parameterBag,
    ) {
        $this->userService = $userService;
        $this->setKey();
        $this->client = $client;
        if (!$parameterBag->has('OZA_URL')) {
            throw new RuntimeException('OZA_URL parameter is not set.');
        }

        $ozaUrl = $parameterBag->get('OZA_URL');
        if (empty($ozaUrl) || !is_string($ozaUrl)) {
            throw new RuntimeException('OZA_URL parameter is empty or invalid.');
        }

        $this->ozaUrl = $ozaUrl;
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
                        'X-Requested-With' => 'XMLHttpRequest',
                    ],
                ],
            );

            $data = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);

            $this->supplies = is_array($data) ? $data : (array)$data;

            return true;
        } catch (ClientException $exception) {
            $this->errorStatus = $exception->getResponse()->getStatusCode();
        } catch (TransportException) {
            $this->errorStatus = 403;
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

    public function setKey(): void
    {
        $this->ozaKey = $this->userService->getUser()->getSettings()->getOzaKey();
    }
}
