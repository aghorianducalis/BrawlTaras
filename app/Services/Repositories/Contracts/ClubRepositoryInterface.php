<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\ClubDTO;
use App\Models\Club;

interface ClubRepositoryInterface
{
    /**
     * Find a club based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return Club|null
     */
    public function findClub(array $searchCriteria): ?Club;

    /**
     * Create or update a single club in the database and sync related entities.
     *
     * @param ClubDTO $clubDTO
     * @return Club
     */
    public function createOrUpdateClub(ClubDTO $clubDTO): Club;
}
