<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Club;
use App\Models\Player;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class ClubRepository implements ClubRepositoryInterface
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function findClub(array $searchCriteria): ?Club
    {
        $query = Club::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['tag'])) {
            $query->where('tag', '=', $searchCriteria['tag']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', '=', $searchCriteria['name']);
        }

        return $query->first();
    }

    public function createOrUpdateClub(ClubDTO $clubDTO): Club
    {
        $club = $this->findClub([
            'tag' => $clubDTO->tag,
        ]);

        $attributes = array_filter([
            'tag' => $clubDTO->tag,
            'name' => $clubDTO->name,
            'description' => $clubDTO->description,
            'type' => $clubDTO->type,
            'badge_id' => $clubDTO->badgeId,
            'required_trophies' => $clubDTO->requiredTrophies,
            'trophies' => $clubDTO->trophies,
        ], fn($value) => !is_null($value));

        DB::transaction(function () use (&$club, $clubDTO, $attributes) {
            // Create or update Club
            if ($club) {
                $club->update(attributes: $attributes);
            } else {
                $club = Club::query()->create(attributes: $attributes);
            }

            // Synchronize a Club's related members.
            if ($clubDTO->members) {
                $this->syncClubMembers($club, $clubDTO->members);
                $club->load(['members']);
            }
        });

        return $club->refresh();
    }

    /**
     * @param Club $club
     * @param PlayerDTO[] $memberDTOs
     * @return Club
     */
    public function syncClubMembers(Club $club, array $memberDTOs): Club
    {
        $memberIds = [];

        foreach ($memberDTOs as $memberDTO) {
            $player = $this->playerRepository->createOrUpdatePlayer(
                playerDTO: $memberDTO,
            );
            $memberIds[] = $player->id;
        }

        // Attach new members
        Player::query()
            ->whereIn('id', $memberIds)
            ->update(['club_id' => $club->id]);

        // Detach players not in $memberIds and attach the new ones
        $club->members()
            ->whereNotIn('id', $memberIds) // Detach old members
            ->update([
                'club_id' => null,
                'club_role' => null,
            ]);

        $club->refresh();
        $club->load(['members']);

        return $club;
    }
}
