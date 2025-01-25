<?php

declare(strict_types=1);

namespace App\API\Contracts;

use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\EventRotationDTO;
use App\API\DTO\Response\ClubPlayerDTO;
use App\API\DTO\Response\PlayerBattleLogDTO;
use App\API\DTO\Response\PlayerDTO;
use App\API\Exceptions\InvalidDTOException;
use App\API\Exceptions\ResponseException;

interface APIClientInterface
{
    /**
     * Fetch a single brawler by its external ID.
     *
     * @param int $externalId
     * @return BrawlerDTO
     * @throws InvalidDTOException
     * @throws ResponseException
     */
    public function getBrawler(int $externalId): BrawlerDTO;

    /**
     * Fetch all brawlers.
     *
     * @return array<BrawlerDTO>
     * @throws InvalidDTOException
     * @throws ResponseException
     */
    public function getBrawlers(): array;

    /**
     * Get information about a single clan by club tag.
     *
     * @param string $clubTag
     * @return ClubDTO
     * @throws InvalidDTOException
     * @throws ResponseException
     */
    public function getClubByTag(string $clubTag): ClubDTO;

    /**
     * List club members.
     *
     * @param string $clubTag
     * @return array<ClubPlayerDTO>
     * @throws InvalidDTOException
     * @throws ResponseException
     */
    public function getClubMembers(string $clubTag): array;

    /**
     * Fetch events rotation.
     *
     * @return array<EventRotationDTO>
     * @throws ResponseException
     * @throws InvalidDTOException
     */
    public function getEventsRotation(): array;

    /**
     * Get information about a single player by player tag.
     *
     * @param string $playerTag
     * @return PlayerDTO
     * @throws InvalidDTOException
     * @throws ResponseException
     */
    public function getPlayerByTag(string $playerTag): PlayerDTO;

    /**
     * Get list of recent battle results for a player.
     *
     * @param string $playerTag
     * @return array<PlayerBattleLogDTO>
     * @throws InvalidDTOException
     * @throws ResponseException
     */
    public function getPlayerBattleLog(string $playerTag): array;
}
