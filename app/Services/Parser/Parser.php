<?php

declare(strict_types=1);

namespace App\Services\Parser;

use App\API\Contracts\APIClientInterface;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;
use App\Models\Brawler;
use App\Services\Parser\Contracts\ParserInterface;
use App\Services\Parser\Exceptions\ParsingException;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

readonly class Parser implements ParserInterface
{
    public function __construct(
        private APIClientInterface         $apiClient,
        private BrawlerRepositoryInterface $brawlerRepository
    ) {}

    public function parseBrawlerByExternalId(int $externalId): Brawler
    {
        try {
            $brawlerDTO = $this->apiClient->getBrawler($externalId);

            return $this->brawlerRepository->createOrUpdateBrawler($brawlerDTO);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Failed to parse Brawler with external ID $externalId: " . $e->getMessage(), [
                'exception' => $e,
                'extId' => $externalId
            ]);
            throw ParsingException::fromException($e);
        }
    }

    public function parseAllBrawlers(): array
    {
        try {
            $brawlerDTOs = $this->apiClient->getBrawlers();

            if (empty($brawlerDTOs)) {
                throw ValidationException::withMessages(['No Brawlers found in the API response.']);
            }

            return $this->brawlerRepository->createOrUpdateBrawlers($brawlerDTOs);
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error('Failed to parse all Brawlers: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw ParsingException::fromException($e);
        }
    }
}
