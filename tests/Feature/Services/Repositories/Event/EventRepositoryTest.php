<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Repositories\Event;

use App\Models\Event;
use App\Models\EventMap;
use App\Models\EventMode;
use App\Models\EventModifier;
use App\Services\Repositories\Contracts\Event\EventRepositoryInterface;
use App\Services\Repositories\Event\EventMapRepository;
use App\Services\Repositories\Event\EventModeRepository;
use App\Services\Repositories\Event\EventModifierRepository;
use App\Services\Repositories\Event\EventRepository;
use Database\Factories\EventFactory;
use Database\Factories\EventMapFactory;
use Database\Factories\EventModeFactory;
use Database\Factories\EventModifierFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use Tests\Traits\CreatesEvents;

#[Group('Repositories')]
#[CoversClass(EventRepository::class)]
#[CoversMethod(EventRepository::class, 'findEvent')]
#[CoversMethod(EventRepository::class, 'createOrUpdateEvent')]
#[CoversClass(EventMapRepository::class)]
#[CoversClass(EventModeRepository::class)]
#[CoversClass(EventModifierRepository::class)]
#[UsesClass(Event::class)]
#[UsesClass(EventMap::class)]
#[UsesClass(EventMode::class)]
#[UsesClass(EventModifier::class)]
#[UsesClass(EventFactory::class)]
#[UsesClass(EventMapFactory::class)]
#[UsesClass(EventModeFactory::class)]
#[UsesClass(EventModifierFactory::class)]
class EventRepositoryTest extends TestCase
{
    use CreatesEvents;
    use RefreshDatabase;

    private EventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(EventRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Create and fetch the event with relations successfully.')]
    #[TestWith(['ext_id', 20251212])]
    public function test_find_event_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing((new Event())->getTable(), [$property => $value]);

        $eventCreated = $this->createEventWithRelations(attributes: [$property => $value]);

        $this->assertDatabaseHas($eventCreated->getTable(), [
            'id' => $eventCreated->id,
            $property => $value,
        ]);

        $eventFound = $this->repository->findEvent([$property => $value]);

        $this->assertEqualEventModels($eventCreated, $eventFound);
    }

    #[Test]
    #[TestDox('Create successfully the event with related entities.')]
    public function test_create_event_with_relations(): void
    {
        $eventDTO = $this->makeEventDTOWithRelations();

        $this->assertDatabaseMissing((new Event())->getTable(), [
            'ext_id' => $eventDTO->id,
        ]);

        $event = $this->repository->createOrUpdateEvent($eventDTO);

        $this->assertDatabaseHas($event->getTable(), [
            'id' => $event->id,
            'ext_id' => $eventDTO->id,
        ]);

        $this->assertEventModelMatchesDTO($event, $eventDTO);
    }

    #[Test]
    #[TestDox('Update successfully the event with related entities.')]
    public function test_update_existing_event(): void
    {
        $event = $this->createEventWithRelations();
        // create DTO to store the new data for event with the same ext ID
        $eventDTO = $this->makeEventDTOWithRelations([
            'ext_id' => $event->ext_id,
        ]);

        $eventUpdated = $this->repository->createOrUpdateEvent($eventDTO);

        $this->assertDatabaseHas($eventUpdated->getTable(), [
            'id' => $eventUpdated->id,
            'ext_id' => $eventDTO->id,
        ]);

        $this->assertEventModelMatchesDTO($eventUpdated, $eventDTO);
    }
}
