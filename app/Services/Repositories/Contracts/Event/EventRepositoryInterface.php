<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts\Event;

use App\API\DTO\Response\EventDTO;
use App\Models\Event;

interface EventRepositoryInterface
{
    /**
     * Find an event based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return Event|null
     */
    public function findEvent(array $searchCriteria): ?Event;

    /**
     * Create or update a single event in the database and sync related entities.
     *
     * @param EventDTO $eventDTO
     * @return Event
     */
    public function createOrUpdateEvent(EventDTO $eventDTO): Event;
}
