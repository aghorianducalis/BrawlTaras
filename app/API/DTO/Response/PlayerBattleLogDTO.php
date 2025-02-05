<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Player;

final readonly class PlayerBattleLogDTO
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
                $data['role'],
                $data['trophies'],
                $data['icon']['id'],
            ) &&
            is_numeric($data['trophies'])
        )) {
            throw InvalidDTOException::fromMessage(
                "Player data array has an invalid structure: " . json_encode($data)
            );
        }

        // Create a new DTO instance
        return new self(
            $data['tag'],
            $data['name'],
            $data['nameColor'],
            $data['role'],
            (int) $data['trophies'],
            $data['icon'],
        );
    }

    /**
     * Factory method to create an array of DTO.
     *
     * @param array $list
     * @return array<int, self>
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromList(array $list): array
    {
        return array_map(fn($item) => self::fromArray($item), $list);
    }

    /**
     * @param Player $player
     * @return self
     */
    public static function fromEloquentModel(Player $player): self
    {
        return self::fromArray(self::eloquentModelToArray(player: $player));
    }

    /**
     * @param array<Player> $players
     * @return self[]
     */
    public static function fromEloquentModels(array $players): array
    {
        return array_map(fn(Player $player) => self::fromEloquentModel($player), $players);
    }

    public static function eloquentModelToArray(Player $player): array
    {
        return [
            'tag' => $player->tag,
            'name' => $player->name,
            'nameColor' => $player->name_color,
            'role' => $player->role ?? '',
            'trophies' => $player->trophies,
            'icon' => [
                'id' => $player->icon_id,
            ],
        ];
    }
}
