<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\AccessoryDTO;
use App\Models\Accessory;

interface AccessoryRepositoryInterface
{
    /**
     * Find an accessory based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return Accessory|null
     */
    public function findAccessory(array $searchCriteria): ?Accessory;

    /**
     * Create or update a single accessory in the database.
     *
     * @param array{ext_id: int, name: string} $accessoryData
     * @return Accessory
     */
    public function createOrUpdateAccessoryFromDataArray(array $accessoryData): Accessory;

    /**
     * Create or update a single accessory in the database.
     *
     * @param AccessoryDTO $accessoryDTO
     * @return Accessory
     */
    public function createOrUpdateAccessoryFromDTO(AccessoryDTO $accessoryDTO): Accessory;
}
