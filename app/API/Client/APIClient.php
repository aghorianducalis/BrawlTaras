<?php

declare(strict_types=1);

namespace App\API\Client;

use App\API\DTO\Response\BrawlerDTO;
use App\API\Enums\APIEndpoints;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\RequestException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

final readonly class APIClient
{
    public string $apiBaseURI;

    private string $apiKey;

    public function __construct(
        protected GuzzleClient $httpClient = new GuzzleClient([]),
    ) {
        $this->apiBaseURI = config('brawlstars_api.api_base_uri');
        $this->apiKey = config('brawlstars_api.api_key');
    }

    /**
     * app(\App\API\Client\APIClient::class)->getBrawlers();
     * @return BrawlerDTO[]
     * @throws RequestException
     * @throws InvalidDTOException
     */
    public function getBrawlers(): array
    {
        $responseData = $this->makeRequest(APIEndpoints::Brawlers);

        return BrawlerDTO::forBrawlersList($responseData);
    }

    /**
     * app(\App\API\Client\APIClient::class)->getBrawler(16000000)
     * @param int $extId
     * @return BrawlerDTO
     * @throws RequestException
     * @throws InvalidDTOException
     */
    public function getBrawler(int $extId): BrawlerDTO
    {
        $responseData = $this->makeRequest(APIEndpoints::BrawlerById, ['id' => $extId]);

        return BrawlerDTO::forBrawler($responseData);
    }

    /**
     * @param APIEndpoints $apiEndpoint
     * @param array $requestData
     * @return array
     * @throws RequestException
     */
    private function makeRequest(APIEndpoints $apiEndpoint, array $requestData = []): array
    {
        $headers = [
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'application/json',
        ];
        $method = $apiEndpoint->method();
        $uri = $this->constructRequestURI($apiEndpoint, $requestData);
        $options = [
            'headers' => $headers,
        ];

        try {
            $response = $this->httpClient->request(
                method: $method,
                uri: $uri,
                options: $options
            );
            $responseData = json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw RequestException::fromGuzzleException($e);
        }

        return $responseData;
    }

    /**
     * Get the valid URI for specified API endpoint.
     *
     * @param APIEndpoints $apiEndpoint
     * @param array $requestData
     * @return string
     */
    public function constructRequestURI(APIEndpoints $apiEndpoint, array $requestData = []): string
    {
        $uri = $this->apiBaseURI . $apiEndpoint->value;

        switch ($apiEndpoint) {
            case APIEndpoints::Brawlers:
                break;
            case APIEndpoints::BrawlerById:
                $uri = str_replace('{brawler_id}', (string) $requestData['id'], $uri);
                break;
            default:
                break;
        }

        return $uri;
    }

    public static function getInstance(): self
    {
        return app(APIClient::class);
    }
}
