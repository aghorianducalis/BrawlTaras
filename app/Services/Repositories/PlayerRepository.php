<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\ClubPlayerDTO;
use App\Models\Player;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class PlayerRepository implements PlayerRepositoryInterface
{
    public function __construct() {}

    public function findPlayer(array $searchCriteria): ?Player
    {
        $query = Player::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['tag'])) {
            $query->where('tag', '=', $searchCriteria['tag']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', '=', $searchCriteria['name']);
        }

        if (isset($searchCriteria['club_id'])) {
            $query->where('club_id', '=', $searchCriteria['club_id']);
        }

        return $query->first();
    }

    public function createOrUpdateClubMember(ClubPlayerDTO $memberDTO): Player
    {
        $player = $this->findPlayer([
            'tag' => $memberDTO->tag,
        ]);

        DB::transaction(function () use (&$player, $memberDTO) {
            // Create or update Player
            $attributes = [
                'tag' => $memberDTO->tag,
                'name' => $memberDTO->name,
                'name_color' => $memberDTO->nameColor,
                'role' => $memberDTO->role, // todo
                'trophies' => $memberDTO->trophies,
                'icon_id' => $memberDTO->icon['id'], // todo
            ];

            if ($player) {
                $player->update(attributes: $attributes);
            } else {
                $player = Player::query()->create(attributes: $attributes);
            }
        });

        return $player->refresh();
    }

    public function createOrUpdateClubMembers(array $memberDTOs): array
    {
        return array_map(fn (ClubPlayerDTO $dto) => $this->createOrUpdateClubMember($dto), $memberDTOs);
    }
}
