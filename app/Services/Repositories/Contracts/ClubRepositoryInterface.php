<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\ClubMemberDTO;
use App\Models\Club;
use App\Models\Player;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
     * Create new or update existed Club from an array of attributes.
     * Validates the attributes and throws an exception on validation error.
     *
     * @param array $attributes input data array
     * @return Club
     * @throws ValidationException
     */
    public function createOrUpdateClubFromArray(array $attributes): Club;

    /**
     * Create or update a single club in the database.
     *
     * @param ClubDTO $clubDTO
     * @return Club
     * @throws ValidationException
     */
    public function createOrUpdateClubFromDTO(ClubDTO $clubDTO): Club;

    /**
     * @param string $tag
     * @return Club
     * @throws ValidationException
     */
    public function createOrUpdateClubFromTag(string $tag): Club;

    /**
     * @param ClubDTO $clubDTO
     * @return Club
     * @throws ValidationException
     */
    public function createOrUpdateClubFromDTOAndSyncClubMembers(ClubDTO $clubDTO): Club;

    /**
     * @param string $tag
     * @param ClubMemberDTO[] $memberDTOs
     * @return Club
     * @throws ValidationException
     */
    public function createOrUpdateClubFromTagAndSyncClubMembers(string $tag, array $memberDTOs): Club;

    /**
     * Sync related players of the club.
     * Bulk create or update club members in the database.
     *
     * @param Club $club
     * @param ClubMemberDTO[] $memberDTOs
     * @return Club
     * @throws ValidationException
     */
    public function syncClubMembers(Club $club, array $memberDTOs): Club;

    /**
     * Create new or update existed member of the club from a DTO.
     * Player is attached to the club by setting its 'club_id' and 'club_role' attributes.
     *
     * @param int $clubId
     * @param ClubMemberDTO $memberDTO
     * @return Player
     * @throws ValidationException
     */
    public function createOrUpdateClubMember(int $clubId, ClubMemberDTO $memberDTO): Player;

    /**
     * @param int $clubId
     * @param array $exceptPlayerIds
     * @return int
     */
    public function detachClubMembers(int $clubId, array $exceptPlayerIds): int;

    /**
     * @return array{tag: array<string>, name: array<string>, description: array<string>, type: array<string>, badgeId: array<string>, trophies: array<string>, requiredTrophies: array<string>, members: array<string>}
     */
    public static function getClubRules(): array;

    /**
     * @return array{tag: array<string>}
     */
    public static function getClubTagRules(): array;

    /**
     * @return array{club_id: array<string>, club_role: array<string|Rule>}
     */
    public static function getClubMemberRules(): array;
}
