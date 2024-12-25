<?php

declare(strict_types=1);

namespace App\API\DTO\Request;

use App\Models\Event;
use App\Models\EventModifier as Modifier;
use JsonException;
use JsonSerializable;

final readonly class EventDTO implements JsonSerializable
{
    private function __construct(public Event $event)
    {}

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $modifiers = $this->event->modifiers
            ->transform(fn(Modifier|array $modifier) => ($modifier instanceof Modifier) ?
                [
                    'name' => $modifier->name,
                ]
                : $modifier
            )
            ->toArray();

        return [
            'id' => $this->event->ext_id,
            'map' => $this->event->map->name,
            'mode' => $this->event->mode->name,
            'modifiers' => $modifiers,
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

    public static function fromEloquentModel(Event $event): self
    {
        return new self($event);
    }
}
