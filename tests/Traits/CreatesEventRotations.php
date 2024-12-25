<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\EventRotationDTO;
use App\Models\EventRotation;

trait CreatesEventRotations
{
    use CreatesEvents;

    /**
     * Create an event rotation with associated event and slot (position).
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @return EventRotation
     */
    public function createEventRotationWithRelations(
        array|callable $attributes = [],
    ) : EventRotation {
        return EventRotation::factory()
            ->create($attributes);
    }

    /**
     * Create an event rotation DTO with related entities stored: event and slot.
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @return EventRotationDTO
     */
    public function makeEventRotationDTOWithRelations(
        array|callable $attributes = [],
    ) : EventRotationDTO {
        $rotation = EventRotation::factory()->make($attributes);

        return EventRotationDTO::fromEloquentModel($rotation);
    }

    public function assertEqualEventRotationModels(EventRotation $rotationExpected, ?EventRotation $rotationActual): void
    {
        $this->assertNotNull($rotationActual);
        $this->assertInstanceOf(EventRotation::class, $rotationActual);
        $this->assertSame($rotationExpected->id, $rotationActual->id);
        $this->assertSame($rotationExpected->start_time->toDateTimeString(), $rotationActual->start_time->toDateTimeString());
        $this->assertSame($rotationExpected->end_time->toDateTimeString(), $rotationActual->end_time->toDateTimeString());
        $this->assertTrue($rotationExpected->created_at->equalTo($rotationActual->created_at));

        // compare the event rotation's relations
        $rotationExpected->load([
            'event',
            'slot',
        ]);
        $rotationActual->load([
            'event',
            'slot',
        ]);

        $this->assertEquals(
            $rotationExpected->event->toArray(),
            $rotationActual->event->toArray()
        );
        $this->assertEquals(
            $rotationExpected->slot->toArray(),
            $rotationActual->slot->toArray()
        );
    }

    public function assertEventRotationModelMatchesDTO(EventRotation $rotation, EventRotationDTO $rotationDTO): void
    {
        $this->assertSame($rotation->start_time->format('Ymd\THis.u\Z'), $rotationDTO->start_time);
        $this->assertSame($rotation->end_time->format('Ymd\THis.u\Z'), $rotationDTO->end_time);

        $rotation->load([
            'event',
            'slot',
        ]);

        $this->assertEventModelMatchesDTO($rotation->event, $rotationDTO->event);
        $this->assertSame($rotation->slot->position, $rotationDTO->slot);
    }
}
