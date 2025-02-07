<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\ClubDTO;
use App\Models\Club;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class ClubRepository implements ClubRepositoryInterface
{
    public function __construct() {}

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
        $attributes = array_filter([
            'tag' => $clubDTO->tag,
            'name' => $clubDTO->name,
            'description' => $clubDTO->description,
            'type' => $clubDTO->type,
            'badge_id' => $clubDTO->badgeId,
            'required_trophies' => $clubDTO->requiredTrophies,
            'trophies' => $clubDTO->trophies,
        ], fn($value) => !is_null($value));

        $club = $this->findClub([
            'tag' => $clubDTO->tag,
        ]);

        DB::transaction(function () use (&$club, $clubDTO, $attributes) {
            // Create or update Club
            if ($club) {
                $club->update(attributes: $attributes);
            } else {
                $club = Club::query()->create(attributes: $attributes);
            }

            // Synchronize a Club's related members
            if (!is_null($clubDTO->members)) {
                $this->syncClubMembers($club, $clubDTO->members);
            }
        });

        return $club->refresh();
    }

    public function syncClubMembers(Club $club, array $playerDTOs): Club
    {
        DB::transaction(function () use (&$club, $playerDTOs) {
            $playerRepository = app(PlayerRepositoryInterface::class);
            $players = collect();

            foreach ($playerDTOs as $memberDTO) {
                $player = $playerRepository->createOrUpdatePlayer($memberDTO);
                $players->add($player);
            }

            // Attach new members
            $club->members()->saveMany($players->all());

            // Detach old members
            $club->members()
                ->whereNotIn('id', $players->pluck('id')->toArray())
                ->update([
                    'club_id' => null,
                    'club_role' => null,
                ]);
        });

        $club->refresh();
        $club->load(['members']);

        return $club;
    }
}
