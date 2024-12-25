<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts\Event;

use App\Models\EventRotationSlot;

interface EventRotationSlotRepositoryInterface
{
    /**
     * Find an event rotation slot based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return EventRotationSlot|null
     */
    public function findEventRotationSlot(array $searchCriteria): ?EventRotationSlot;

    /**
     * Create or update a single event rotation slot in the database.
     *
     * @param int|string $slotPosition
     * @return EventRotationSlot
     */
    public function createOrUpdateEventRotationSlot(int|string $slotPosition): EventRotationSlot;
}
