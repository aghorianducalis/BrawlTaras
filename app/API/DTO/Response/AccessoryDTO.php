<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;

final readonly class AccessoryDTO
{
    private function __construct(
        public int $extId,
        public string $name
    ) {}

    /**
     * @return array{ext_id: int, name: string}
     */
    public function toArray(): array
    {
        return [
            'ext_id' => $this->extId,
            'name' => $this->name,
        ];
    }

    /**
     * Factory method to create an AccessoryDTO.
     *
     * @param array $data
     * @return self
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromArray(array $data): self
    {
        if (!(isset($data['id']) && is_numeric($data['id']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'id' field in Accessory data");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in Accessory data");
        }

        return new self((int)$data['id'], $data['name']);
    }

    /**
     * Factory method to create an array of AccessoryDTO.
     *
     * @param array $list
     * @return array<int, AccessoryDTO>
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromList(array $list): array
    {
        return array_map(fn($item) => self::fromArray($item), $list);
    }
}
