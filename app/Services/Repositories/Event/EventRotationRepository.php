<?php

declare(strict_types=1);

namespace App\Services\Repositories\Event;

use App\API\DTO\Response\EventRotationDTO;
use App\Models\EventRotation;
use App\Services\Repositories\Contracts\Event\EventRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRotationRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRotationSlotRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class EventRotationRepository implements EventRotationRepositoryInterface
{
    public function __construct(
        private EventRepositoryInterface             $eventRepository,
        private EventRotationSlotRepositoryInterface $slotRepository,
    ) {}

    public function findEventRotation(array $searchCriteria): ?EventRotation
    {
        $query = EventRotation::query();

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

        // nice todo more flexible search by datetime
        if (isset($searchCriteria['start_time'])) {
            $query->where('start_time', '=', $searchCriteria['start_time']);
        }

        if (isset($searchCriteria['end_time'])) {
            $query->where('end_time', '=', $searchCriteria['end_time']);
        }

        return $query->first();
    }

    /**
     * @throws Exception
     */
    public function createOrUpdateEventRotation(EventRotationDTO $rotationDTO): EventRotation
    {
        $rotation = null;

        DB::transaction(function () use (&$rotation, $rotationDTO) {
            $startTime = Carbon::createFromFormat($rotationDTO->start_time, 'Ymd\THis.u\Z')->toDateTimeString();
            $endTime = Carbon::createFromFormat($rotationDTO->end_time, 'Ymd\THis.u\Z')->toDateTimeString();
            $event = $this->eventRepository->createOrUpdateEvent($rotationDTO->event);
            $slot = $this->slotRepository->createOrUpdateEventRotationSlot($rotationDTO->slot);

            $rotation = $this->findEventRotation([
                'start_time' => $startTime,
                'end_time' => $endTime,
                'position' => $rotationDTO->slot,
            ]);
            $attributes = [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'event_id' => $event->id,
                'slot_id' => $slot->id,
            ];

            if ($rotation) {
                $rotation->update(attributes: $attributes);
            } else {
                $rotation = EventRotation::query()->create(attributes: $attributes);
            }
        });

        if (!$rotation) {
            throw new Exception("DB transaction failed while trying to create or update event rotation.");
        }

        return $rotation->refresh();
    }

    /**
     * @inheritdoc
     */
    public function createOrUpdateEventRotations(array $rotationDTOs): array
    {
        return array_map(fn (EventRotationDTO $dto) => $this->createOrUpdateEventRotation($dto), $rotationDTOs);
    }
}
