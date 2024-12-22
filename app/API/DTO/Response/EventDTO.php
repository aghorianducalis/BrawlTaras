<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Event;
use App\Models\EventModifier as Modifier;

final readonly class EventDTO
{
    /**
     * @param int $id
     * @param string $map
     * @param string $mode
     * @param array<string> $modifiers
     */
    private function __construct(
        public int $id,
        public string $map,
        public string $mode,
        public array $modifiers,
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
            isset($data['id'], $data['map'], $data['mode']) ||
            !is_array($data['modifiers'] ?? null) // todo validate nested elements to be strings
        )) {
            throw InvalidDTOException::fromMessage(
                "Event data array has an invalid structure: " . json_encode($data)
            );
        }

        // Create a new DTO instance
        return new self(
            (int) $data['id'],
            $data['map'],
            $data['mode'],
            $data['modifiers'] ?? [],
        );
    }

    /**
     * Factory method to create an array of DTO.
     *
     * @param array $list Raw parsed data containing an array of event entities.
     * @return array<int, EventDTO> Array of DTO instances converted from the raw data array.
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromList(array $list): array
    {
        return array_map(fn(array $item) => self::fromArray($item), $list);
    }

    /**
     * @param Event $event
     * @return self
     */
    public static function fromEloquentModel(Event $event): self
    {
        return self::fromArray([
            'id' => $event->ext_id,
            'map' => $event->map->name,
            'mode' => $event->mode->name,
            'modifiers' => $event->modifiers->transform(fn (Modifier $modifier) => [
                'name' => $modifier->name,
            ])->toArray(),
        ]);
    }
}
