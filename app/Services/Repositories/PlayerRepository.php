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

        if (isset($searchCriteria['club_role'])) {
            $query->where('club_role', '=', $searchCriteria['club_role']);
        }

        return $query->first();
    }

    public function createOrUpdatePlayer(PlayerDTO $playerDTO): Player
    {
        $attributes = array_filter([
            'tag' => $playerDTO->tag,
            'name' => $playerDTO->name,
            'name_color' => $playerDTO->nameColor,
            'club_role' => $playerDTO->clubRole,
            'trophies' => $playerDTO->trophies,
            'icon_id' => $playerDTO->icon['id'],
        ], fn($value) => !is_null($value));

        $player = $this->findPlayer([
            'tag' => $playerDTO->tag,
        ]);

        DB::transaction(function () use (&$player, $playerDTO, $attributes) {
            if ($player) {
                $player->update(attributes: $attributes);
            } else {
                $player = Player::query()->create(attributes: $attributes);
            }
        });

        return $player->refresh();
    }

    public function syncClubForPlayer(PlayerDTO $playerDTO): ?Club
    {
        $club = null;

        if ($playerDTO->club && $playerDTO->clubRole) {
            $clubDTO = ClubDTO::fromDataArray($playerDTO->club);

            $club = app(ClubRepositoryInterface::class)->createOrUpdateClub($clubDTO);
        }

        return $club;
    }
}
