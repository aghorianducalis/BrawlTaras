<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;

final readonly class BattlePlayerBrawlerDTO
{
    /**
     * @param int      $id           // Brawler's external ID
     * @param string   $name         // Brawler's name
     * @param int      $power
     * @param int      $trophies
     * @param int|null $trophyChange
     */
    private function __construct(
        public int    $id,
        public string $name,
        public int    $power,
        public int    $trophies,
        public ?int   $trophyChange,
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
        if (!(isset($data['id']) && is_numeric($data['id']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'id' field in player's battle brawler data."
            );
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'name' field in player's battle brawler data."
            );
        }

        if (!(isset($data['power']) && is_numeric($data['power']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'power' field in player's battle brawler data."
            );
        }

        if (!(isset($data['trophies']) && is_numeric($data['trophies']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'trophies' field in player's battle brawler data."
            );
        }

        if (key_exists('trophyChange', $data) && !is_numeric($data['trophyChange'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'trophyChange' field in player's battle brawler data."
            );
        }

        return new self(
            id:           (int) $data['id'],
            name:         $data['name'],
            power:        (int) $data['power'],
            trophies:     (int) $data['trophies'],
            trophyChange: isset($data['trophyChange']) ? (int) $data['trophyChange'] : null,
        );
    }

    /**
     * Factory method to create an array of DTO.
     *
     * @param array<array> $list
     * @return array<self>
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromArrayList(array $list): array
    {
        return array_map(fn(array $item) => self::fromArray($item), $list);
    }
}
