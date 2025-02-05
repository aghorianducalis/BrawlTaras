<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\PlayerDTO;
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
     * Create or update a single club in the database.
     *
     * @param ClubDTO $clubDTO
     * @return Club
     */
    public function createOrUpdateClub(ClubDTO $clubDTO): Club;

    /**
     * Sync related players of the club.
     * Bulk create or update club members in the database.
     *
     * @param Club $club
     * @param PlayerDTO[] $memberDTOs
     * @return Club
     */
    public function syncClubMembers(Club $club, array $memberDTOs): Club;
}
