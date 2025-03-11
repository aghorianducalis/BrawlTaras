<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\PlayerBrawlerDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Player;
use Illuminate\Validation\ValidationException;
use JsonException;

// todo check exceptions
interface PlayerRepositoryInterface
{
    /**
     * Find a player based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return Player|null
     */
    public function findPlayer(array $searchCriteria): ?Player;

    /**
     * Create or update (if existed) Player model from an array of attributes.
     * Validates the attributes and throws an exception on validation error.
     *
     * @param array $attributes input data array
     * @return Player
     * @throws ValidationException
     */
    public function createOrUpdatePlayerFromDataArray(array $attributes): Player;

    /**
     * Create or update a single player in the database.
     *
     * @param PlayerDTO $playerDTO
     * @return Player
     * @throws ValidationException
     */
    public function createOrUpdatePlayerFromDTO(PlayerDTO $playerDTO): Player;

    /**
     * Create or update a single player in the database and sync related entities.
     *
     * @param PlayerDTO $playerDTO
     * @return Player
     * @throws JsonException
     * @throws ValidationException
     */
    public function createOrUpdatePlayerFromDTOAndSyncRelations(PlayerDTO $playerDTO): Player;

    /**
     * Sync the player's relations:
     * 1. Brawlers with related accessories, gears and star powers;
     * 2. Club.
     *
     * @param Player $player
     * @param PlayerBrawlerDTO[]|array{} $playerBrawlerDTOs
     * @param array{tag: string, name: string}|array{} $clubDataArray is either empty array (if player does not belong to any club), or has required 'tag', 'name' keys.
     * @return void
     * @throws ValidationException
     */
    public function syncPlayerRelations(Player $player, array $playerBrawlerDTOs = [], array $clubDataArray = []): void;

    /**
     * Sync the player's relation with a Club. Player is:
     * - either attached to specified Club,
     * - or detached from any club, if no club specified (=== null).
     *
     * @param Player $player
     * @param array{tag: string, name: string}|array{} $clubDataArray is either empty array (if player does not belong to any club), or has required 'tag', 'name' keys.
     * @return bool
     * @throws ValidationException
     */
    public function syncPlayerClub(Player $player, array $clubDataArray = []): bool;

    /**
     * Sync the related brawlers owned by a player.
     * Bulk create or update player brawlers with accessories, gears and star powers in the database.
     *
     * @param Player $player
     * @param PlayerBrawlerDTO[] $playerBrawlerDTOs
     * @return void
     */
    public function syncPlayerBrawlers(Player $player, array $playerBrawlerDTOs): void;

    /**
     * @return array{tag: array<string>, name: array<string>, name_color: array<string>, icon_id: array<string>, trophies: array<string>, highest_trophies: array<string>, club_id: array<string>, club_role: array<string>}
     */
    public static function getPlayerRules(): array;
}
