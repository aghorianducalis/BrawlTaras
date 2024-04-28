<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;

final readonly class BrawlerDTO
{
    public int $extId;

    public string $name;

    /** @var AccessoryDTO[] */
    public array $accessories;

    /** @var StarPowerDTO[] */
    public array $starPowers;

    public function __construct(
        int $extId,
        string $name,
        array $accessories,
        array $starPowers,
    ) {
        $this->extId = $extId;
        $this->name = $name;
        $this->accessories = $accessories;
        $this->starPowers = $starPowers;
    }

    /**
     * Factory method to create BrawlerDTO from an array.
     *
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function forBrawler(array $rawResponseData): self
    {
        // validate the response structure
        if (! (
            (array_keys($rawResponseData) === ['id', 'name', 'starPowers', 'gadgets']) &&
            is_array($rawResponseData['starPowers']) &&
            is_array($rawResponseData['gadgets'])
        )) {
            throw InvalidDTOException::fromString("API response for brawler by id has invalid structure");
        }

        // validate the brawler's base properties
        if (!isset($rawResponseData['id']) || !is_numeric($rawResponseData['id'])) {
            throw InvalidDTOException::fromString("Invalid or missing 'id' field in Brawler data");
        }

        if (
            !isset($rawResponseData['name']) ||
            !is_string($rawResponseData['name']) ||
            (trim($rawResponseData['name']) === '')
        ) {
            throw InvalidDTOException::fromString("Invalid or missing 'name' field in Brawler data");
        }

        // validate the brawler's accessories
        if (!isset($rawResponseData['gadgets']) || !is_array($rawResponseData['gadgets'])) {
            throw InvalidDTOException::fromString("Invalid or missing 'gadgets' field in Brawler data");
        }

        $accessories = AccessoryDTO::forAccessoryList($rawResponseData['gadgets']);

        // validate the brawler's star powers
        if (!isset($rawResponseData['starPowers']) || !is_array($rawResponseData['starPowers'])) {
            throw InvalidDTOException::fromString("Invalid or missing 'starPowers' field in Brawler data");
        }

        $starPowers = StarPowerDTO::forStarPowerList($rawResponseData['starPowers']);

        return new self(
            (int)$rawResponseData['id'],
            $rawResponseData['name'],
            $accessories,
            $starPowers,
        );
    }

    /**
     * Factory method to create an array of BrawlerDTO from parsed raw data.
     *
     * @param array $brawlerListData raw parsed data containing an array of brawler entities.
     * @return array<int, BrawlerDTO> array of BrawlerDTO converted from the passed raw data array.
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function forBrawlersList(array $brawlerListData): array
    {
        // validate the response structure
        if (array_keys($brawlerListData) !== ['items', 'paging']) {
            throw InvalidDTOException::fromString("API response for list of brawlers has invalid structure");
        }

        $brawlerDTOs = [];

        foreach ($brawlerListData['items'] as $brawlerData) {
            $brawlerDTOs[] = BrawlerDTO::forBrawler($brawlerData);
        }

        return $brawlerDTOs;
    }
}
