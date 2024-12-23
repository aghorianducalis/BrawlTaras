<?php

declare(strict_types=1);

namespace App\Services\Repositories\Contracts\Event;

use App\Models\EventModifier;

interface EventModifierRepositoryInterface
{
    /**
     * Find an event modifier based on search criteria (attributes).
     *
     * @param array $searchCriteria
     * @return EventModifier|null
     */
    public function findEventModifier(array $searchCriteria): ?EventModifier;

    /**
     * Create or update a single event modifier in the database.
     *
     * @param string $name
     * @return EventModifier
     */
    public function createOrUpdateEventModifier(string $name): EventModifier;
}
