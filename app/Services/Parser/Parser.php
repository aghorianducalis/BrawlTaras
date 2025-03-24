<?php

declare(strict_types=1);

namespace App\Services\Parser;

use App\API\Contracts\APIClientInterface;

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
            return $this->brawlerRepository->createOrUpdateBrawlerFromDTOAndSyncRelations($brawlerDTO);
        } catch (ResponseException|InvalidDTOException $e) {
            Log::error("Failed to parse Brawler with external ID $externalId: " . $e->getMessage(), [
                'exception' => $e,
                'extId' => $externalId,
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

            return $this->brawlerRepository->createOrUpdateBrawlersFromDTOsAndSyncRelations($brawlerDTOs);
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error('Failed to parse all Brawlers: ' . $e->getMessage(), [
                'exception' => $e,
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
                'exception' => $e,
            ]);
            throw ParsingException::fromException($e);
        }
    }

    public function parseClubMembers(string $clubTag): Club
    {
        try {
            $memberDTOs = $this->apiClient->getClubMembers($clubTag);
            return $this->clubRepository->createOrUpdateClubFromTagAndSyncClubMembers($clubTag, $memberDTOs);
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error("Failed to parse members of Club with tag $clubTag: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw ParsingException::fromException($e);
        }
    }

    public function parsePlayerByTag(string $playerTag): Player
    {
        try {
            $playerDTO = $this->apiClient->getPlayerByTag($playerTag);
            return $this->playerRepository->createOrUpdatePlayerFromDTOAndSyncRelations(playerDTO: $playerDTO);
        } catch (ResponseException|InvalidDTOException|ValidationException $e) {
            Log::error("Failed to parse Player with tag $playerTag: " . $e->getMessage(), [
                'exception' => $e,
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
                'exception' => $e,
            ]);
            throw ParsingException::fromException($e);
        }
    }

    /**
     * @throws ParsingException|ResponseException
     */
    public function test(): void
    {
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->test();
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->parseBrawlerByExternalId((int) env('BS_BRAWLER_EXT_ID'));
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->parseAllBrawlers();
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->parseClubByTag(env('BS_CLUB_TAG'));
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->parseClubMembers(env('BS_CLUB_TAG'));
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->parsePlayerByTag(env('BS_PLAYER_TAG'));
        // app(\App\Services\Parser\Contracts\ParserInterface::class)->parseEventsRotation();

        $brawler = $this->parseBrawlerByExternalId((int) env('BS_BRAWLER_EXT_ID'));
        $brawlers = $this->parseAllBrawlers();
        $club = $this->parseClubByTag(env('BS_CLUB_TAG'));
        $clubMembers = $this->parseClubMembers(env('BS_CLUB_TAG'));
        $player = $this->parsePlayerByTag(env('BS_PLAYER_TAG'));
//        $player = $this->parsePlayerByTag(env('BS_PLAYER_WITHOUT_CLUB_TAG'));
        $events = $this->parseEventsRotation();
        $battleLog = $this->apiClient->getPlayerBattleLog(env('BS_PLAYER_TAG'));

        dd(
            start:       'THIS IS THE START OF DD',
            brawler:     $brawler,
            brawlers:    $brawlers,
            club:        $club,
            clubMembers: $clubMembers,
            player:      $player,
            events:      $events,
            battleLog:   $battleLog,
            end:         'THIS IS THE END OF DD',
        );
    }
}
