<?php

declare(strict_types=1);

namespace App\Services\Repositories\Event;

use App\API\DTO\Response\EventDTO;
use App\Models\Event;
use App\Services\Repositories\Contracts\Event\EventMapRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventModeRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventModifierRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class EventRepository implements EventRepositoryInterface
{
    public function __construct(
        private EventMapRepositoryInterface      $mapRepository,
        private EventModeRepositoryInterface     $modeRepository,
        private EventModifierRepositoryInterface $modifierRepository,
    ) {}

    public function findEvent(array $searchCriteria): ?Event
    {
        $query = Event::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['ext_id'])) {
            $query->where('ext_id', '=', $searchCriteria['ext_id']);
        }

        if (isset($searchCriteria['map_id'])) {
            $query->where('map_id', '=', $searchCriteria['map_id']);
        }

        if (isset($searchCriteria['mode_id'])) {
            $query->where('mode_id', '=', $searchCriteria['mode_id']);
        }

        return $query->first();
    }

    public function findOrCreateEventFromDTO(EventDTO $eventDTO): Event
    {
        $event = $this->findEvent([
            'ext_id' => $eventDTO->id,
        ]);

        return $event ?? $this->createEventFromDTO(eventDTO: $eventDTO);
    }

    public function createEventFromDTO(EventDTO $eventDTO): Event
    {
        $event = null;

        DB::transaction(function () use (&$event, $eventDTO) {
            // Create an Event's related entities: map, mode and modifiers.
            $map = $this->mapRepository->createOrUpdateEventMap($eventDTO->map);
            $mode = $this->modeRepository->createOrUpdateEventMode($eventDTO->mode);

            $modifierIds = [];

            foreach ($eventDTO->modifiers as $modifier) {
                $modifier = $this->modifierRepository->createOrUpdateEventModifier($modifier);
                $modifierIds[] = $modifier->id;
            }

            $attributes = [
                'ext_id'  => $eventDTO->id,
                'map_id'  => $map->id,
                'mode_id' => $mode->id,
            ];

            $event = Event::query()->create(attributes: $attributes);

            // Attach an Event's related entities: map, mode and modifiers.
            $event->map()->associate($map);
            $event->mode()->associate($mode);
            $event->modifiers()->attach($modifierIds);
        });

        return $event->refresh();
    }

    public function createOrUpdateEventFromDTO(EventDTO $eventDTO): Event
    {
        // todo rewrite this method to create or update without find
        $event = $this->findEvent([
            'ext_id' => $eventDTO->id,
        ]);

        DB::transaction(function () use (&$event, $eventDTO) {
            // Create an Event's related entities: map, mode and modifiers.
            $map = $this->mapRepository->createOrUpdateEventMap($eventDTO->map);
            $mode = $this->modeRepository->createOrUpdateEventMode($eventDTO->mode);

            $modifierIds = [];

            foreach ($eventDTO->modifiers as $modifier) {
                $modifier = $this->modifierRepository->createOrUpdateEventModifier($modifier);
                $modifierIds[] = $modifier->id;
            }

            // Create or update Event
            $attributes = [
                'ext_id' => $eventDTO->id,
                'map_id' => $map->id,
                'mode_id' => $mode->id,
            ];

            if ($event) {
                $event->update(attributes: $attributes);
            } else {
                $event = Event::query()->create(attributes: $attributes);
            }

            // Synchronize an Event's related entities: map, mode and modifiers.
            $event->map()->associate($map);
            $event->mode()->associate($mode);
            $event->modifiers()->sync($modifierIds);
        });

        return $event->refresh();
    }
}
