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

    public function createOrUpdatePlayer(ClubPlayerDTO $playerDTO): Player
    {
        $player = $this->findPlayer([
            'tag' => $playerDTO->tag,
        ]);

        DB::transaction(function () use (&$player, $playerDTO) {
            // Create or update Player
            $attributes = [
                'tag' => $playerDTO->tag,
                'name' => $playerDTO->name,
                'name_color' => $playerDTO->nameColor,
                'role' => $playerDTO->role, // todo
                'trophies' => $playerDTO->trophies,
                'icon_id' => $playerDTO->icon['id'], // todo
            ];

            if ($player) {
                $player->update(attributes: $attributes);
            } else {
                $player = Player::query()->create(attributes: $attributes);
            }
        });

        return $player->refresh();
    }

    public function createOrUpdatePlayers(array $playerDTOs): array
    {
        return array_map(fn (ClubPlayerDTO $dto) => $this->createOrUpdatePlayer($dto), $playerDTOs);
    }
}