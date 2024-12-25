<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts\Event;

use App\Models\EventMap;

interface EventMapRepositoryInterface
{
    /**
     * Find an event map based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return EventMap|null
     */
    public function findEventMap(array $searchCriteria): ?EventMap;

    /**
     * Create or update a single event map in the database.
     *
     * @param string $name
     * @return EventMap
     */
    public function createOrUpdateEventMap(string $name): EventMap;
}
