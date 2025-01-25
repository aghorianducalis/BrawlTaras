<?php

declare(strict_types=1);

namespace App\API\Client;

use App\API\Contracts\APIClientInterface;
use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\EventRotationDTO;
use App\API\DTO\Response\ClubPlayerDTO;
use App\API\DTO\Response\PlayerBattleLogDTO;
use App\API\DTO\Response\PlayerDTO;
use App\API\Enums\APIEndpoints;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;

final readonly class APIClient implements APIClientInterface
{
    public function __construct(
        protected HttpClient $httpClient,
        protected string     $apiBaseURI,
        protected string     $apiKey
    ) {}

    /**
     * app(\App\API\Client\APIClient::class)->getBrawler(16000000)
     *
     * @inheritdoc
     */
    public function getBrawler(int $externalId): BrawlerDTO
    {
        try {
            $responseData = $this->makeRequest(APIEndpoints::BrawlerById, ['brawler_id' => $externalId]);
            return BrawlerDTO::fromArray($responseData);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching brawler with ID $externalId: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * app(\App\API\Client\APIClient::class)->getBrawlers();
     *
     * @inheritdoc
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

    public function getClubByTag(string $clubTag): ClubDTO
    {
        try {
            $clubTag = $this->prepareTagValue($clubTag);
            $responseData = $this->makeRequest(APIEndpoints::ClubByTag, ['club_tag' => $clubTag]);
            return ClubDTO::fromArray($responseData);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching club with tag $clubTag: {$e->getMessage()}");
            throw $e;
        }
    }

    public function getClubMembers(string $clubTag): array
    {
        try {
            $clubTag = $this->prepareTagValue($clubTag);
            $responseData = $this->makeRequest(APIEndpoints::ClubMembers, ['club_tag' => $clubTag]);

            if (!(isset($responseData['items']) && is_array($responseData['items']))) {
                throw InvalidDTOException::fromMessage('invalid structure of club members.');
            }

            return ClubPlayerDTO::fromList($responseData['items']);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching members of club with tag $clubTag: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function getEventsRotation(): array
    {
        try {
            $responseData = $this->makeRequest(APIEndpoints::EventRotation);
            return EventRotationDTO::fromList($responseData);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching events rotation: {$e->getMessage()}");
            throw $e;
        }
    }

    public function getPlayerByTag(string $playerTag): PlayerDTO
    {
        try {
            $playerTag = $this->prepareTagValue($playerTag);
            $responseData = $this->makeRequest(APIEndpoints::PlayerByTag, ['player_tag' => $playerTag]);

            return PlayerDTO::fromArray($responseData);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching info of player with tag $playerTag: {$e->getMessage()}");
            throw $e;
        }
    }

    public function getPlayerBattleLog(string $playerTag): array
    {
        try {
            $playerTag = $this->prepareTagValue($playerTag);
            $responseData = $this->makeRequest(APIEndpoints::PlayerBattleLog, ['player_tag' => $playerTag]);

            if (!(isset($responseData['items']) && is_array($responseData['items']))) {
                throw InvalidDTOException::fromMessage('invalid structure of player battle log.');
            }

            // todo BattleResult or Battle DTO
            return PlayerBattleLogDTO::fromList($responseData['items']);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Error fetching battle log of player with tag $playerTag: {$e->getMessage()}");
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

    private function prepareTagValue(string $value): string
    {
        // todo ensure club or player tag starts with "%23" instead of "#"
        return $value;
    }
}
