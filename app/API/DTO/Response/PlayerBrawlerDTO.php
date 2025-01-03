<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Player;

final readonly class PlayerBrawlerDTO
{
    /**
     * @param int $extId
     * @param string $name
     * @param int $power
     * @param int $rank
     * @param int $trophies
     * @param int $highestTrophies
     * @param array<array{ext_id: int, name: string, level: int}> $gears
//     * @param array<array{ext_id: int, name: string}> $starPowers
//     * @param array<array{ext_id: int, name: string}> $gadgets
     * @param array<StarPowerDTO> $starPowers
     * @param array<AccessoryDTO> $gadgets
     */
    private function __construct(
        public int $extId,
        public string $name,
        public int $power,
        public int $rank,
        public int $trophies,
        public int $highestTrophies,
        public array $gears,
        public array $starPowers,
        public array $gadgets,
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
                $data['role'],// todo enum or model
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
     * @return array<self>
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
            'role' => $player->role ?? '',// todo enum, column of players table or intermediate (club_player.role) if many-to-many
            'trophies' => $player->trophies,
            'icon' => [
                'id' => $player->icon_id,
            ],
        ];
    }
}
