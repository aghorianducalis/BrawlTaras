<?php

declare(strict_types=1);

namespace App\Services\Parser;

use App\API\Contracts\APIClientInterface;
use App\API\DTO\Response\ClubDTO;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;
use App\Models\Brawler;
use App\Models\Club;
use App\Models\Player;
use App\Services\Parser\Contracts\ParserInterface;
use App\Services\Parser\Exceptions\ParsingException;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRotationRepositoryInterface;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

readonly class Parser implements ParserInterface
{
    public function __construct(
        private APIClientInterface               $apiClient,
        private BrawlerRepositoryInterface       $brawlerRepository,
        private ClubRepositoryInterface          $clubRepository,
        private PlayerRepositoryInterface        $playerRepository,
        private EventRotationRepositoryInterface $eventRotationRepository,
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

    public function parseClubByTag(string $clubTag): Club
    {
        try {
            $clubDTO = $this->apiClient->getClubByTag($clubTag);

            return $this->clubRepository->createOrUpdateClubFromDTOAndSyncClubMembers($clubDTO);
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error("Failed to parse Club with tag $clubTag: " . $e->getMessage(), [
                'exception' => $e
            ]);
            throw ParsingException::fromException($e);
        }
    }

    /**
     * @throws ParsingException
     */
    public function parseClubMembers(string $clubTag): Club
    {
        try {
            $memberDTOs = $this->apiClient->getClubMembers($clubTag);

            return $this->clubRepository->createOrUpdateClubFromTagAndSyncClubMembers($clubTag, $memberDTOs);
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error("Failed to parse members of Club with tag $clubTag: " . $e->getMessage(), [
                'exception' => $e
            ]);
            throw ParsingException::fromException($e);
        }
    }

    public function parseEventsRotation(): array
    {
        try {
            $rotationDTOs = $this->apiClient->getEventsRotation();

            if (empty($rotationDTOs)) {
                throw ValidationException::withMessages(['No events rotation found in the API response.']);
            }

            return $this->eventRotationRepository->createOrUpdateEventRotations($rotationDTOs);
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error('Failed to parse events rotation: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw ParsingException::fromException($e);
        }
    }

    public function parsePlayerByTag(string $playerTag): Player
    {
        try {
            $playerDTO = $this->apiClient->getPlayerByTag($playerTag);
            $player = $this->playerRepository->createOrUpdatePlayerFromTagAndSyncRelations(
                tag: $playerTag,
                playerDTO: $playerDTO,
            );

            return $player;
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error("Failed to parse Player with tag $playerTag: " . $e->getMessage(), [
                'exception' => $e
            ]);
            throw ParsingException::fromException($e);
        }
    }

    /**
     * @throws ParsingException
     */
    public function test(): void
    {
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->test();
        $player = $this->parsePlayerByTag(env('BS_PLAYER_TAG'));
//        $player = $this->parsePlayerByTag(env('BS_PLAYER_WITHOUT_CLUB_TAG'));
    }
}
