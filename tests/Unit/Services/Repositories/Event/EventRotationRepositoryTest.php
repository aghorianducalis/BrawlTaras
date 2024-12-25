<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Repositories\Event;

use App\Models\Event;
use App\Models\EventRotation;
use App\Models\EventRotationSlot;
use App\Services\Repositories\Contracts\Event\EventRotationRepositoryInterface;
use App\Services\Repositories\Event\EventRepository;
use App\Services\Repositories\Event\EventRotationRepository;
use App\Services\Repositories\Event\EventRotationSlotRepository;
use Carbon\Carbon;
use Database\Factories\EventFactory;
use Database\Factories\EventRotationFactory;
use Database\Factories\EventRotationSlotFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use Tests\Traits\CreatesEventRotations;

#[Group('Repositories')]
#[CoversClass(EventRotationRepository::class)]
#[CoversMethod(EventRotationRepository::class, 'findEventRotation')]
#[CoversMethod(EventRotationRepository::class, 'createOrUpdateEventRotation')]
#[CoversMethod(EventRotationRepository::class, 'createOrUpdateEventRotations')]
#[CoversClass(EventRotationSlotRepository::class)]
#[CoversClass(EventRepository::class)]
#[UsesClass(Event::class)]
#[UsesClass(EventRotation::class)]
#[UsesClass(EventRotationSlot::class)]
#[UsesClass(EventFactory::class)]
#[UsesClass(EventRotationFactory::class)]
#[UsesClass(EventRotationSlotFactory::class)]
class EventRotationRepositoryTest extends TestCase
{
    use CreatesEventRotations;
    use RefreshDatabase;

    private EventRotationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(EventRotationRepositoryInterface::class);
    }

    #[Test]
    #[TestDox('Create and fetch the event rotation with relations successfully.')]
    #[TestWith(['start_time', '1999-01-01 00:00:01'])]
    #[TestWith(['end_time', '2025-12-12 20:25:00'])]
    public function test_find_event_rotation_by_criteria(string $property, int|string $value): void
    {
        $this->assertDatabaseMissing((new EventRotation())->getTable(), [$property => $value]);

        $rotationCreated = $this->createEventRotationWithRelations(attributes: [$property => $value]);

        $this->assertDatabaseHas($rotationCreated->getTable(), [
            'id' => $rotationCreated->id,
            $property => $value,
        ]);

        $rotationFound = $this->repository->findEventRotation([$property => $value]);

        $this->assertEqualEventRotationModels($rotationCreated, $rotationFound);
    }

    #[Test]
    #[TestDox('Create successfully the event rotation with related entities.')]
    public function test_create_event_rotation_with_relations(): void
    {
        $rotationDTO = $this->makeEventRotationDTOWithRelations();
        $startTime = Carbon::createFromFormat('Ymd\THis.u\Z', $rotationDTO->start_time)->toDateTimeString();
        $endTime = Carbon::createFromFormat('Ymd\THis.u\Z', $rotationDTO->end_time)->toDateTimeString();


        $this->assertDatabaseMissing((new EventRotation())->getTable(), [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $rotation = $this->repository->createOrUpdateEventRotation($rotationDTO);

        $this->assertDatabaseHas($rotation->getTable(), [
            'id' => $rotation->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $this->assertEventRotationModelMatchesDTO($rotation, $rotationDTO);
    }

    #[Test]
    #[TestDox('Update successfully the event rotation with related entities.')]
    public function test_update_existing_event_rotation(): void
    {
        $rotation = $this->createEventRotationWithRelations();
        // create DTO to store the new data for event rotation with the same start and end time
        $rotationDTO = $this->makeEventRotationDTOWithRelations([
            'start_time' => $rotation->start_time->toDateTimeString(),
            'end_time' => $rotation->end_time->toDateTimeString(),
        ]);

        $rotationUpdated = $this->repository->createOrUpdateEventRotation($rotationDTO);

        $this->assertDatabaseHas($rotationUpdated->getTable(), [
            'id' => $rotationUpdated->id,
            'start_time' => $rotation->start_time->toDateTimeString(),
            'end_time' => $rotation->end_time->toDateTimeString(),
        ]);

        $this->assertEventRotationModelMatchesDTO($rotationUpdated, $rotationDTO);
    }
}
