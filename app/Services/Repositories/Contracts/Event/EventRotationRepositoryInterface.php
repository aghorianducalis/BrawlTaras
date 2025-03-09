<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts\Event;

use App\API\DTO\Response\EventRotationDTO;
use App\Models\EventRotation;

interface EventRotationRepositoryInterface
{
    /**
     * Find an event rotation based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return EventRotation|null
     */
    public function findEventRotation(array $searchCriteria): ?EventRotation;

    /**
     * Create or update a single event rotation in the database and sync related entities.
     *
     * @param EventRotationDTO $rotationDTO
     * @return EventRotation
     */
    public function createOrUpdateEventRotation(EventRotationDTO $rotationDTO): EventRotation;

    /**
     * Bulk create or update event rotations in the database and sync related entities.
     *
     * @param array<EventRotationDTO> $rotationDTOs
     * @return array<EventRotation>
     */
    public function createOrUpdateEventRotations(array $rotationDTOs): array;
}
