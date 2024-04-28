<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;

final readonly class AccessoryDTO
{
    public int $extId;

    public string $name;

    public function __construct(int $extId, string $name)
    {
        $this->extId = $extId;
        $this->name = $name;
    }

    /**
     * Factory method to create AccessoryDTO from an array.
     *
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function forAccessory(array $accessoryData): self
    {
        if (!(isset($accessoryData['id']) && is_numeric($accessoryData['id']))) {
            throw InvalidDTOException::fromString("Invalid or missing 'id' field in Accessory data");
        }

        if (!(isset($accessoryData['name']) && is_string($accessoryData['name']) && !empty(trim($accessoryData['name'])))) {
            throw InvalidDTOException::fromString("Invalid or missing 'name' field in Accessory data");
        }

        return new self((int)$accessoryData['id'], $accessoryData['name']);
    }

    /**
     * Factory method to create an array of AccessoryDTO.
     *
     * @param array $accessoryListData
     * @return array<int, AccessoryDTO>
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function forAccessoryList(array $accessoryListData): array
    {
        $accessoryDTOs = [];

        foreach ($accessoryListData as $accessoryData) {
            $accessoryDTOs[] = self::forAccessory($accessoryData);
        }

        return $accessoryDTOs;
    }
}
