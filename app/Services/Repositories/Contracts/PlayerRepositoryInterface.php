<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\PlayerDTO;
use App\Models\Player;

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
     * Create or update a single player in the database.
     *
     * @param PlayerDTO $playerDTO
     * @return Player
     */
    public function createOrUpdatePlayer(PlayerDTO $playerDTO): Player;
}
