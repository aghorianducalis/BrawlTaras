<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts;

use App\API\DTO\Response\GearDTO;
use App\Models\Gear;

interface GearRepositoryInterface
{
    /**
     * Find a gear based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return Gear|null
     */
    public function findGear(array $searchCriteria): ?Gear;

    /**
     * Create or update a single gear in the database.
     *
     * @param array{ext_id: int, name: string} $gearData
     * @return Gear
     */
    public function createOrUpdateGearFromDataArray(array $gearData): Gear;

    /**
     * Create or update a single gear in the database.
     *
     * @param GearDTO $gearDTO
     * @return Gear
     */
    public function createOrUpdateGearFromDTO(GearDTO $gearDTO): Gear;
}
