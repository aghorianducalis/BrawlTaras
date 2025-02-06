<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Gear;

final readonly class GearDTO
{
    private function __construct(
        public int $extId,
        public string $name,
        public int $level,
    ) {}

    /**
     * @return array{extId: string, name: string, level: string}
     */
    public function toArray(): array
    {
        return [
            'extId' => $this->extId,
            'name' => $this->name,
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
            throw InvalidDTOException::fromMessage("Invalid or missing 'id' field in Gear data");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in Gear data");
        }

        if (!(isset($data['level']) && is_numeric($data['level']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'level' field in Gear data");
        }

        return new self(
            extId: (int) $data['id'],
            name: $data['name'],
            level: (int) $data['level'],
        );
    }

    /**
     * Factory method to create an array of GearDTO.
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
     * @param Gear $gear
     * @return self
     */
    public static function fromEloquentModel(Gear $gear): self
    {
        return new self(
            extId: $gear->ext_id,
            name: $gear->name,
            level: $gear->level,
        );
    }
}
