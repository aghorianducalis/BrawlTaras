<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\StarPower;

final readonly class BrawlerDTO
{
    /**
     * @param int $extId
     * @param string $name
     * @param AccessoryDTO[] $accessories
     * @param StarPowerDTO[] $starPowers
     */
    private function __construct(
        public int $extId,
        public string $name,
        public array $accessories,
        public array $starPowers,
    ) {}

    /**
     * Factory method to create BrawlerDTO.
     *
     * @param array $data
     * @return self
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromArray(array $data): self
    {
        // Validate the structure of the data array
        if (!(
            isset($data['id'], $data['name'], $data['gadgets'], $data['starPowers']) &&
            is_array($data['gadgets']) &&
            is_array($data['starPowers'])
        )) {
            throw InvalidDTOException::fromMessage(
                "Brawler data array has an invalid structure: " . json_encode($data)
            );
        }

        // Create a new BrawlerDTO instance
        return new self(
            (int) $data['id'],
            $data['name'],
            AccessoryDTO::fromList($data['gadgets']),
            StarPowerDTO::fromList($data['starPowers']),
        );
    }

    /**
     * Factory method to create an array of BrawlerDTO.
     *
     * @param array $list Raw parsed data containing an array of brawler entities.
     * @return array<int, BrawlerDTO> Array of BrawlerDTO instances converted from the raw data array.
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromList(array $list): array
    {
        // Validate the structure of the list
        if (!isset($list['items']) || !is_array($list['items'])) {
            throw InvalidDTOException::fromMessage("Invalid Brawler list data");
        }

        return array_map(fn(array $item) => self::fromArray($item), $list['items']);
    }

    /**
     * @param Brawler $brawler
     * @return self
     */
    public static function fromBrawlerModel(Brawler $brawler): self
    {
        return self::fromArray([
            'id' => $brawler->ext_id,
            'name' => $brawler->name,
            'gadgets' => $brawler->accessories->transform(fn (Accessory $accessory) => [
                'id' => $accessory->ext_id,
                'name' => $accessory->name,
            ])->toArray(),
            'starPowers' => $brawler->starPowers->transform(fn (StarPower $starPower) => [
                'id' => $starPower->ext_id,
                'name' => $starPower->name,
            ])->toArray(),
        ]);
    }
}
