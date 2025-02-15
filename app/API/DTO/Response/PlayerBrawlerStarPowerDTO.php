<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\PlayerBrawlerStarPower;

final readonly class PlayerBrawlerStarPowerDTO
{
    private function __construct(
        public int $extId,
        public string $name,
    ) {}

    /**
     * @return array{extId: int, name: string}
     */
    public function toArray(): array
    {
        return [
            'extId' => $this->extId,
            'name'  => $this->name,
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
            throw InvalidDTOException::fromMessage("Invalid or missing 'id' field in player brawler StarPower data");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in player brawler StarPower data");
        }

        return new self(
            extId: (int) $data['id'],
            name: $data['name'],
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
     * Factory method to create DTO from Eloquent model.
     *
     * @param PlayerBrawlerStarPower $playerBrawlerStarPower
     * @return self
     */
    public static function fromEloquentModel(PlayerBrawlerStarPower $playerBrawlerStarPower): self
    {
        if (is_null($playerBrawlerStarPower->starPower)) {
            throw InvalidDTOException::fromMessage("There is no StarPower associated with player brawler star power: {$playerBrawlerStarPower->toJson()}");
        }

        return new self(
            extId: $playerBrawlerStarPower->starPower->ext_id,
            name: $playerBrawlerStarPower->starPower->name,
        );
    }
}
