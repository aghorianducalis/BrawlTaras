<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\StarPowerDTO;
use App\Models\StarPower;

interface StarPowerRepositoryInterface
{
    /**
     * Find a star power based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return StarPower|null
     */
    public function findStarPower(array $searchCriteria): ?StarPower;

    /**
     * Create or update a single star power in the database.
     *
     * @param StarPowerDTO $starPowerDTO
     * @param int $brawlerId
     * @return StarPower
     */
    public function createOrUpdateStarPower(StarPowerDTO $starPowerDTO, int $brawlerId): StarPower;
}
