<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts\Event;

use App\Models\EventMode;

interface EventModeRepositoryInterface
{
    /**
     * Find an event mode based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return EventMode|null
     */
    public function findEventMode(array $searchCriteria): ?EventMode;

    /**
     * Create or update a single event mode in the database.
     *
     * @param string $name
     * @return EventMode
     */
    public function createOrUpdateEventMode(string $name): EventMode;
}
