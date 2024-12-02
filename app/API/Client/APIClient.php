<?php

declare(strict_types=1);

namespace App\API\Client;

use App\API\DTO\Response\BrawlerDTO;
use App\API\Enums\APIEndpoints;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;

final readonly class APIClient
{
    public function __construct(
        protected GuzzleClient $httpClient,
        protected string $apiBaseURI,
        protected string $apiKey
    ) {
        //
    }

    /**
     * Fetch all brawlers.
     *
     * app(\App\API\Client\APIClient::class)->getBrawlers();
     *
     * @return BrawlerDTO[]
     * @throws ResponseException
     * @throws InvalidDTOException
     */
    public function getBrawlers(): array
    {
        try {
            $responseData = $this->makeRequest(APIEndpoints::Brawlers);
            return BrawlerDTO::fromList($responseData);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching brawlers: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Fetch a brawler by its ID.
     *
     * app(\App\API\Client\APIClient::class)->getBrawler(16000000)
     *
     * @param int $extId
     * @return BrawlerDTO
     * @throws ResponseException
     * @throws InvalidDTOException
     */
    public function getBrawler(int $extId): BrawlerDTO
    {
        try {
            $responseData = $this->makeRequest(APIEndpoints::BrawlerById, ['brawler_id' => $extId]);
            return BrawlerDTO::fromArray($responseData);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching brawler with ID $extId: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Make a request to the API.
     *
     * @param APIEndpoints $apiEndpoint
     * @param array $requestData
     * @param array $options Additional request options like query params or body.
     * @return array
     * @throws ResponseException
     */
    private function makeRequest(APIEndpoints $apiEndpoint, array $requestData = [], array $options = []): array
    {
        $headers = [
            'Authorization' => "Bearer $this->apiKey",
            'Accept'        => 'application/json',
        ];
        $method = $apiEndpoint->method();
        $uri = $apiEndpoint->constructRequestURI(apiBaseURI: $this->apiBaseURI, requestData: $requestData);
        $requestOptions = array_merge($options, ['headers' => $headers]);

        try {
            $response = $this->httpClient->request(
                method: $method,
                uri: $uri,
                options: $requestOptions
            );
            $responseBody = $response->getBody()->getContents();

            return json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            Log::error("API request failed", [
                'uri' => $uri,
                'error' => $e->getMessage(),
            ]);
            throw ResponseException::fromException($e);
        } catch (JsonException $e) {
            Log::error("Failed to decode JSON response", [
                'uri' => $uri,
                'error' => $e->getMessage(),
            ]);
            throw ResponseException::fromMessage('Invalid JSON response from API');
        }
    }
}
