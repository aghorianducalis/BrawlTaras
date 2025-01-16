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
     * @param array<GearDTO> $gears
     * @param array<StarPowerDTO> $starPowers
     * @param array<AccessoryDTO> $accessories
     */
    private function __construct(
        public int    $extId,
        public string $name,
        public int    $power,
        public int    $rank,
        public int    $trophies,
        public int    $highestTrophies,
        public array  $gears,
        public array  $starPowers,
        public array  $accessories,
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
        if (!(isset($data['id']) && is_numeric($data['id']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'id' field in player's brawler data");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in player's brawler data");
        }

        if (!(isset($data['power']) && is_numeric($data['power']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'power' field in player's brawler data");
        }

        if (!(isset($data['rank']) && is_numeric($data['rank']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'power' field in player's brawler data");
        }

        if (!(isset($data['trophies']) && is_numeric($data['trophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'trophies' field in player's brawler data");
        }

        if (!(isset($data['highestTrophies']) && is_numeric($data['highestTrophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'highestTrophies' field in player's brawler data");
        }

        if (!(isset($data['gears']) && is_array($data['gears']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'gears' field in player's brawler data");
        }

        if (!(isset($data['starPowers']) && is_array($data['starPowers']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'starPowers' field in player's brawler data");
        }

        if (!(isset($data['gadgets']) && is_array($data['gadgets']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'gadgets' field in player's brawler data");
        }

        // Create a new DTO instance
        return new self(
            extId: (int) $data['id'],
            name: $data['name'],
            power: (int) $data['power'],
            rank: (int) $data['rank'],
            trophies: (int) $data['trophies'],
            highestTrophies: (int) $data['highestTrophies'],
            gears: GearDTO::fromList($data['gears']),
            starPowers: StarPowerDTO::fromList($data['starPowers']),
            accessories: AccessoryDTO::fromList($data['gadgets']),
        );
    }

    /**
     * Factory method to create an array of DTO.
     *
     * @param array $list
     * @return array<self>
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
