<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;

final readonly class StarPowerDTO
{
    public int $extId;

    public string $name;

    public function __construct(int $extId, string $name)
    {
        $this->extId = $extId;
        $this->name = $name;
    }

    /**
     * Factory method to create StarPowerDTO from an array.
     *
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function forStarPower(array $starPowerData): self
    {
        if (!(isset($starPowerData['id']) && is_numeric($starPowerData['id']))) {
            throw InvalidDTOException::fromString("Invalid or missing 'id' field in StarPower data");
        }

        if (!(isset($starPowerData['name']) && is_string($starPowerData['name']) && !empty(trim($starPowerData['name'])))) {
            throw InvalidDTOException::fromString("Invalid or missing 'name' field in StarPower data");
        }

        return new self((int)$starPowerData['id'], $starPowerData['name']);
    }

    /**
     * Factory method to create an array of StarPowerDTO.
     *
     * @param array $starPowerListData
     * @return array<int, StarPowerDTO>
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function forStarPowerList(array $starPowerListData): array
    {
        $starPowerDTOs = [];

        foreach ($starPowerListData as $starPowerData) {
            $starPowerDTOs[] = self::forStarPower($starPowerData);
        }

        return $starPowerDTOs;
    }
}
