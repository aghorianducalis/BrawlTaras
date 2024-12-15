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
     * @param AccessoryDTO $accessoryDTO
     * @param int $brawlerId
     * @return Accessory
     */
    public function createOrUpdateAccessory(AccessoryDTO $accessoryDTO, int $brawlerId): Accessory;
}
