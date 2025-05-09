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
     * Create a single event with related entities in the database.
     *
     * @param EventDTO $eventDTO
     * @return Event
     */
    public function createEventFromDTO(EventDTO $eventDTO): Event;

    /**
     * Find or create a single event with related entities in the database.
     *
     * @param EventDTO $eventDTO
     * @return Event
     */
    public function findOrCreateEventFromDTO(EventDTO $eventDTO): Event;

    /**
     * Create or update a single event in the database and sync related entities.
     *
     * @param EventDTO $eventDTO
     * @return Event
     */
    public function createOrUpdateEventFromDTO(EventDTO $eventDTO): Event;
}
