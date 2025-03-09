<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\PlayerBrawlerDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Player;
use Illuminate\Validation\ValidationException;

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
    public function createOrUpdatePlayerFromArray(array $attributes): Player;

    /**
     * Create or update a single player in the database.
     *
     * @param PlayerDTO $playerDTO
     * @return Player
     * @throws ValidationException
     */
    public function createOrUpdatePlayerFromDTO(PlayerDTO $playerDTO): Player;

    /**
     * @param string $tag
     * @param PlayerDTO $playerDTO
     * @return Player
     * @throws ValidationException
     */
    public function createOrUpdatePlayerFromTagAndSyncRelations(string $tag, PlayerDTO $playerDTO): Player;

    /**
     * Sync related brawlers owned by player.
     * Bulk create or update player brawlers with accessories, gears and star powers in the database.
     *
     * @param Player $player
     * @param PlayerBrawlerDTO[] $playerBrawlerDTOs
     * @return Player
     */
    public function syncPlayerBrawlers(Player $player, array $playerBrawlerDTOs): Player;

    /**
     * @return array{tag: array<string>, name: array<string>, name_color: array<string>, icon_id: array<string>, trophies: array<string>, highest_trophies: array<string>, club_id: array<string>, club_role: array<string>}
     */
    public static function getPlayerRules(): array;
}
