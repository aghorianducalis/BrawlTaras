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

    public function createOrUpdateEvent(EventDTO $eventDTO): Event
    {
        $event = $this->findEvent([
            'ext_id' => $eventDTO->id,
        ]);
        $attributes = [
            'ext_id' => $eventDTO->id,
        ];

        DB::transaction(function () use (&$event, $eventDTO, $attributes) {
            if ($event) {
                $event->update(attributes: $attributes);
            } else {
                $event = Event::query()->create(attributes: $attributes);
            }

            $this->syncRelations($event, $eventDTO);
        });

        return $event->refresh();
    }

    /**
     * Synchronize an Event's related entities: map, mode and modifiers.
     *
     * @param Event $event
     * @param EventDTO $eventDTO
     * @return void
     */
    private function syncRelations(Event $event, EventDTO $eventDTO): void
    {
        $map = $this->mapRepository->createOrUpdateEventMap($eventDTO->map);
        $event->map()->associate($map);

        $mode = $this->modeRepository->createOrUpdateEventMode($eventDTO->mode);
        $event->mode()->associate($mode);

        $modifierIds = [];

        foreach ($eventDTO->modifiers as $modifier) {
            $modifier = $this->modifierRepository->createOrUpdateEventModifier($modifier);
            $modifierIds[] = $modifier->id;
        }

        $event->modifiers()->sync($modifierIds);
    }
}
