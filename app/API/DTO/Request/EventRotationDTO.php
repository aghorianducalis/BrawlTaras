<?php

declare(strict_types=1);

namespace App\API\DTO\Request;

use App\Models\EventRotation;
use JsonException;
use JsonSerializable;

final readonly class EventRotationDTO implements JsonSerializable
{
    private function __construct(public EventRotation $rotation)
    {}

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'startTime' => $this->rotation->start_time->format('Ymd\THis.u\Z'),
            'endTime' => $this->rotation->end_time->format('Ymd\THis.u\Z'),
            'slotId' => $this->rotation->slot->position,
            'event' => EventDTO::fromEloquentModel($this->rotation->event),
        ];
    }

    /**
     * Converts the object to a JSON string.
     *
     * @return false|string
     * @throws JsonException
     */
    public function toJson(): false|string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    public static function fromEloquentModel(EventRotation $rotation): self
    {
        return new self($rotation);
    }
}
