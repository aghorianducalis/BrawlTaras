<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\PlayerBrawlerGear;

final readonly class PlayerBrawlerGearDTO
{
    private function __construct(
        public int $extId,
        public string $name,
        public int $level,
    ) {}

    /**
     * @return array{extId: int, name: string, level: int}
     */
    public function toArray(): array
    {
        return [
            'extId' => $this->extId,
            'name'  => $this->name,
            'level' => $this->level,
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
            throw InvalidDTOException::fromMessage("Invalid or missing 'id' field in player brawler Gear data");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in player brawler Gear data");
        }

        if (!(isset($data['level']) && is_numeric($data['level']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'level' field in player brawler Gear data");
        }

        return new self(
            extId: (int) $data['id'],
            name: $data['name'],
            level: (int) $data['level'],
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

    /**
     * Factory method to create DTO from Eloquent model.
     *
     * @param PlayerBrawlerGear $playerBrawlerGear
     * @return self
     */
    public static function fromEloquentModel(PlayerBrawlerGear $playerBrawlerGear): self
    {
        if (!$playerBrawlerGear->gear) {
            throw InvalidDTOException::fromMessage("There is no Gear associated with player brawler gear: {$playerBrawlerGear->toJson()}");
        }

        return new self(
            extId: $playerBrawlerGear->gear->ext_id,
            name: $playerBrawlerGear->gear->name,
            level: $playerBrawlerGear->level,
        );
    }
}
