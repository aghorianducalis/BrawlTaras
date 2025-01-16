<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\ClubPlayerDTO;
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
     * Create or update in the database a single member of the club.
     *
     * @param ClubPlayerDTO $memberDTO
     * @return Player
     */
    public function createOrUpdateClubMember(ClubPlayerDTO $memberDTO): Player;

    /**
     * Bulk create or update club members in the database.
     *
     * @param array<ClubPlayerDTO> $memberDTOs
     * @return array<Player>
     */
    public function createOrUpdateClubMembers(array $memberDTOs): array;
}
