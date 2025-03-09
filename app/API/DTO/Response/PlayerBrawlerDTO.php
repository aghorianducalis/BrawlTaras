<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\PlayerBrawler;
use App\Models\PlayerBrawlerAccessory;
use App\Models\PlayerBrawlerGear;
use App\Models\PlayerBrawlerStarPower;

final readonly class PlayerBrawlerDTO
{
    /**
     * @param int $extId
     * @param string $name
     * @param int $power
     * @param int $rank
     * @param int $trophies
     * @param int $highestTrophies
     * @param array<PlayerBrawlerAccessoryDTO> $accessories
     * @param array<PlayerBrawlerGearDTO> $gears
     * @param array<PlayerBrawlerStarPowerDTO> $starPowers
     */
    private function __construct(
        public int    $extId,
        public string $name,
        public int    $power,
        public int    $rank,
        public int    $trophies,
        public int    $highestTrophies,
        public array  $accessories,
        public array  $gears,
        public array  $starPowers,
    ) {}

    /**
     * @return array{extId: int, name: string, power: int, rank: int, trophies: int, highestTrophies: int, gadgets: array<array{extId: int, name: string}>, gears: array<array{extId: int, name: string, level: int}>, starPowers: array<array{extId: int, name: string}>}
     */
    public function toArray(): array
    {
        return [
            'extId' => $this->extId,
            'name' => $this->name,
            'power' => $this->power,
            'rank' => $this->rank,
            'trophies' => $this->trophies,
            'highestTrophies' => $this->highestTrophies,
            'gadgets' => array_map(fn(PlayerBrawlerAccessoryDTO $accessoryDTO) => $accessoryDTO->toArray(), $this->accessories),
            'gears' => array_map(fn(PlayerBrawlerGearDTO $gearDTO) => $gearDTO->toArray(), $this->gears),
            'starPowers' => array_map(fn(PlayerBrawlerStarPowerDTO $starPowerDTO) => $starPowerDTO->toArray(), $this->starPowers),
        ];
    }

    /**
     * Factory method to create DTO.
     *
     * @param array $data
     * @return self
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromArray(array $data): self
    {
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

        if (!(isset($data['gadgets']) && is_array($data['gadgets']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'gadgets' field in player's brawler data");
        }

        if (!(isset($data['gears']) && is_array($data['gears']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'gears' field in player's brawler data");
        }

        if (!(isset($data['starPowers']) && is_array($data['starPowers']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'starPowers' field in player's brawler data");
        }

        return new self(
            extId: (int) $data['id'],
            name: $data['name'],
            power: (int) $data['power'],
            rank: (int) $data['rank'],
            trophies: (int) $data['trophies'],
            highestTrophies: (int) $data['highestTrophies'],
            accessories: PlayerBrawlerAccessoryDTO::fromList($data['gadgets']),
            gears: PlayerBrawlerGearDTO::fromList($data['gears']),
            starPowers: PlayerBrawlerStarPowerDTO::fromList($data['starPowers']),
        );
    }

    public static function fromEloquentModel(PlayerBrawler $playerBrawler): self
    {
        return new self(
            extId: $playerBrawler->brawler->ext_id,
            name: $playerBrawler->brawler->name,
            power: $playerBrawler->power,
            rank: $playerBrawler->rank,
            trophies: $playerBrawler->trophies,
            highestTrophies: $playerBrawler->highest_trophies,
            accessories: array_map(fn(PlayerBrawlerAccessory $accessory) => PlayerBrawlerAccessoryDTO::fromEloquentModel($accessory), $playerBrawler->playerBrawlerAccessories->all()),
            gears: array_map(fn(PlayerBrawlerGear $gear) => PlayerBrawlerGearDTO::fromEloquentModel($gear), $playerBrawler->playerBrawlerGears->all()),
            starPowers: array_map(fn(PlayerBrawlerStarPower $starPower) => PlayerBrawlerStarPowerDTO::fromEloquentModel($starPower), $playerBrawler->playerBrawlerStarPowers->all()),
        );
    }
}
