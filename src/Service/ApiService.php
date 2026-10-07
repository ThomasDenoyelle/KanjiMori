<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService
{
    public function __construct(
        #[Autowire('%hono_api_key%')]
        private readonly string $apiKey,

        #[Autowire('%hono_api_url%')]
        private readonly string $apiUrl,

        private readonly HttpClientInterface $httpClientInterface,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function analyseText(string $text): array
    {
        try {
            $response = $this->httpClientInterface->request('POST', $this->apiUrl . '/generate', [
                'headers' => [
                    'X-API-KEY' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'text' => $text,
                ],
            ]);

            $responseText = $response->toArray();

            return $responseText['vocabulary'] ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Error while analyzing text: ' . $e->getMessage());
            return [];
        }
    }
}
