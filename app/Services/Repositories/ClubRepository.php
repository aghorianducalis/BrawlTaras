<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\ClubDTO;
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

        DB::transaction(function () use (&$club, $clubDTO) {
            // Create or update Club
            $attributes = [
                'tag' => $clubDTO->tag,
                'name' => $clubDTO->name,
                'description' => $clubDTO->description,
                'type' => $clubDTO->type,
                'badge_id' => $clubDTO->badgeId,
                'required_trophies' => $clubDTO->requiredTrophies,
                'trophies' => $clubDTO->trophies,
            ];

            if ($club) {
                $club->update(attributes: $attributes);
            } else {
                $club = Club::query()->create(attributes: $attributes);
            }

            // Synchronize a Club's related members.
            $memberIds = [];

            foreach ($clubDTO->members as $memberDTO) {
                $player = $this->playerRepository->createOrUpdateClubMember(memberDTO: $memberDTO);
                $memberIds[] = $player->id;
            }

            // Detach players not in $memberIds and attach the new ones
            $club->members()
                ->whereNotIn('id', $memberIds) // Detach old members
                ->update(['club_id' => null]);

            Player::query()
                ->whereIn('id', $memberIds) // Attach new members
                ->update(['club_id' => $club->id]);
        });

        return $club->refresh();
    }
}
