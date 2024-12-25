<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\EventDTO;
use App\Models\Event;
use App\Models\EventModifier;

trait CreatesEvents
{
    /**
     * Create an event with associated event map, mode and modifiers.
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @param int $modifierCount
     * @return Event
     */
    public function createEventWithRelations(
        array|callable $attributes = [],
        int $modifierCount = 2
    ) : Event {
        return Event::factory()
            ->withModifiers($modifierCount)
            ->create($attributes);
    }

    /**
     * Create an event DTO with related entities stored: event map, mode and modifiers.
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @param int $modifierCount
     * @return EventDTO
     */
    public function makeEventDTOWithRelations(
        array|callable $attributes = [],
        int            $modifierCount = 2,
    ) : EventDTO {
        /** @var Event $event */
        $event = Event::factory()->make($attributes);

        // store the related entities in DB
        $event->map->save();
        $event->mode->save();
        $modifiers = EventModifier::factory()
            ->count($modifierCount)
            ->create();

        return EventDTO::fromArray([
            'id' => $event->ext_id,
            'map' => $event->map->name,
            'mode' => $event->mode->name,
            'modifiers' => $modifiers->transform(fn (EventModifier $modifier) => $modifier->name)->toArray(),
        ]);
    }

    public function assertEqualEventModels(Event $eventExpected, ?Event $eventActual): void
    {
        $this->assertNotNull($eventActual);
        $this->assertInstanceOf(Event::class, $eventActual);
        $this->assertSame($eventExpected->id, $eventActual->id);
        $this->assertSame($eventExpected->ext_id, $eventActual->ext_id);
        $this->assertSame($eventExpected->map_id, $eventActual->map_id);
        $this->assertSame($eventExpected->mode_id, $eventActual->mode_id);
        $this->assertTrue($eventExpected->created_at->equalTo($eventActual->created_at));

        // compare the event's relations
        $eventExpected->load([
            'map',
            'mode',
            'modifiers',
        ]);
        $eventActual->load([
            'map',
            'mode',
            'modifiers',
        ]);

        $this->assertEquals(
            $eventExpected->map->toArray(),
            $eventActual->map->toArray()
        );
        $this->assertEquals(
            $eventExpected->mode->toArray(),
            $eventActual->mode->toArray()
        );
        $this->assertEquals(
            $eventExpected->modifiers->toArray(),
            $eventActual->modifiers->toArray()
        );
    }

    public function assertEventModelMatchesDTO(Event $event, EventDTO $eventDTO): void
    {
        $this->assertSame($event->ext_id, $eventDTO->id);

        $event->load([
            'map',
            'mode',
            'modifiers',
        ]);

        $this->assertSame($event->map->name, $eventDTO->map);
        $this->assertSame($event->mode->name, $eventDTO->mode);
        $this->assertEquals(
            $event->modifiers->pluck('name')->toArray(),
            $eventDTO->modifiers,
        );
    }
}
