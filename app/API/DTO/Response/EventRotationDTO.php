<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\EventRotation;

final readonly class EventRotationDTO
{
    private function __construct(
        public string   $start_time,
        public string   $end_time,
        public EventDTO $event,
        public int      $slot,
    ) {}

    /**
     * Factory method to create DTO.
     *
     * @param array $data
     * @return self
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromArray(array $data): self
    {
        // Validate the structure of the data array
        if (!(
            isset($data['startTime'], $data['endTime'], $data['event'], $data['slotId']) &&
            is_string($data['startTime']) &&
            is_string($data['endTime']) &&
            is_array($data['event']) &&
            is_numeric($data['slotId'])
        )) {
            throw InvalidDTOException::fromMessage(
                "Events rotation data array has an invalid structure: " . json_encode($data)
            );
        }

        // Create a new DTO instance
        return new self(
            start_time: $data['startTime'],
            end_time: $data['endTime'],
            event: EventDTO::fromArray($data['event']),
            slot: (int) $data['slotId'],
        );
    }

    /**
     * Factory method to create an array of DTO.
     *
     * @param array $list Raw parsed data containing an array of event rotation entities.
     * @return array<int, EventRotationDTO> Array of DTO instances converted from the raw data array.
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromList(array $list): array
    {
        return array_map(fn(array $item) => self::fromArray($item), $list);
    }

    /**
     * @param EventRotation $rotation
     * @return self
     */
    public static function fromEloquentModel(EventRotation $rotation): self
    {
        return self::fromArray([
            'start_time' => $rotation->start_time->format('Ymd\THis.u\Z'),
            'end_time' => $rotation->start_time->format('Ymd\THis.u\Z'),
            'event' => EventDTO::fromEloquentModel($rotation->event),
            'id' => $rotation->slot->position,
        ]);
    }
}
