<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Player;

final readonly class PlayerDTO
{
    /**
     * @param string $tag
     * @param string $name
     * @param string $nameColor
     * @param array{id: int} $icon
     * @param int $trophies
     * @param int $highestTrophies
     * @param int $expLevel
     * @param int $expPoints
     * @param bool $isQualifiedFromChampionshipChallenge
     * @param int $victoriesSolo
     * @param int $victoriesDuo
     * @param int $victories3vs3
     * @param int $bestRoboRumbleTime
     * @param int $bestTimeAsBigBrawler
     * @param array{tag: string, name: string} $club
     * @param array<PlayerBrawlerDTO> $brawlers
     */
    private function __construct(
        public string $tag,
        public string $name,
        public string $nameColor,
        public array $icon,
        public int $trophies,
        public int $highestTrophies,
        public int $expLevel,
        public int $expPoints,
        public bool $isQualifiedFromChampionshipChallenge,
        public int $victoriesSolo,
        public int $victoriesDuo,
        public int $victories3vs3,
        public int $bestRoboRumbleTime,
        public int $bestTimeAsBigBrawler,
        public array $club,
        public array $brawlers,
    ) {}

    /**
     * Factory method to create DTO.
     *
     * @param array $data
     * @return self
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromArray(array $data): self
    {
        // Validate the structure of the data array
        if (!(
            isset(
                $data['tag'],
                $data['name'],
                $data['nameColor'],
                $data['icon'],
                $data['trophies'],
                $data['highestTrophies'],
                $data['expLevel'],
                $data['expPoints'],
                $data['isQualifiedFromChampionshipChallenge'],
                $data['3vs3Victories'],
                $data['soloVictories'],
                $data['duoVictories'],
                $data['bestRoboRumbleTime'],
                $data['bestTimeAsBigBrawler'],
                $data['club'],
                $data['brawlers'],
            ) &&
            (is_array($data['icon']) && isset($data['icon']['id']) && is_numeric($data['icon']['id'])) &&
            is_numeric($data['trophies']) &&
            is_numeric($data['highestTrophies']) &&
            is_numeric($data['expLevel']) &&
            is_numeric($data['expPoints']) &&
            is_bool($data['isQualifiedFromChampionshipChallenge']) &&
            is_numeric($data['3vs3Victories']) &&
            is_numeric($data['soloVictories']) &&
            is_numeric($data['duoVictories']) &&
            is_numeric($data['bestTimeAsBigBrawler']) &&
            (is_array($data['club']) && isset($data['club']['tag'], $data['club']['name'])) &&
            (is_array($data['brawlers']))
        )) {
            throw InvalidDTOException::fromMessage(
                "Player data array has an invalid structure: " . json_encode($data)
            );
        }

        // Create a new DTO instance
        return new self(
            tag: $data['tag'],
            name: $data['name'],
            nameColor: $data['nameColor'],
            icon: $data['icon'],
            trophies: (int) $data['trophies'],
            highestTrophies: (int) $data['highestTrophies'],
            expLevel: (int) $data['expLevel'],
            expPoints: (int) $data['expPoints'],
            isQualifiedFromChampionshipChallenge: (bool) $data['isQualifiedFromChampionshipChallenge'],
            victoriesSolo: (int) $data['soloVictories'],
            victoriesDuo: (int) $data['duoVictories'],
            victories3vs3: (int) $data['3vs3Victories'],
            bestRoboRumbleTime: (int) $data['bestRoboRumbleTime'],
            bestTimeAsBigBrawler: (int) $data['bestTimeAsBigBrawler'],
            club: [
                'tag' => $data['club']['tag'],
                'name' => $data['club']['name'],
            ],
            brawlers: PlayerBrawlerDTO::fromList($data['brawlers']),
        );
    }

    /**
     * @param Player $player
     * @return self
     */
    public static function fromEloquentModel(Player $player): self
    {
        return self::fromArray(self::eloquentModelToArray(player: $player));
    }

    public static function eloquentModelToArray(Player $player): array
    {
        return [
            'tag' => $player->tag,
            'name' => $player->name,
            'nameColor' => $player->name_color,
            'icon' => [
                'id' => $player->icon_id,
            ],
            'trophies' => $player->trophies,
            'highestTrophies' => $player->highest_trophies,
            'expLevel' => $player->exp_level,
            'expPoints' => $player->exp_points,
            'isQualifiedFromChampionshipChallenge' => $player->is_qualified_from_championship_league,
            'soloVictories' => $player->solo_victories,
            'duoVictories' => $player->duo_victories,
            '3vs3Victories' => $player->trio_victories,
            'bestRoboRumbleTime' => $player->best_time_robo_rumble,
            'bestTimeAsBigBrawler' => $player->best_time_as_big_brawler,
            'club' => $player->club ? [
                'tag' => $player->club->tag,
                'name' => $player->club->name,
            ] : [],
            'brawlers' => $player->playerBrawlers ? $player->playerBrawlers->toArray() : [],
        ];
    }
}
