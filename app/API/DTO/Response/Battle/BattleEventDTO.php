<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Event;

final readonly class BattleEventDTO
{
    /**
     * @param int    $id
     * @param string $map
     * @param string $mode
     */
    private function __construct(
        public int    $id,
        public string $map,
        public string $mode,
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
        if (!(isset($data['id']) && is_numeric($data['id']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'id' field in player's battle log event data."
            );
        }

        if (!(isset($data['map']) && is_string($data['map']) && !empty(trim($data['map'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'map' field in player's battle log event data."
            );
        }

        if (!(isset($data['mode']) && is_string($data['mode']) && !empty(trim($data['mode'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'mode' field in player's battle log event data."
            );
        }

        return new self(
            id:   (int) $data['id'],
            map:  $data['map'],
            mode: $data['mode'],
        );
    }

    /**
     * @param Event $event
     * @return self
     */
    public static function fromEloquentModel(Event $event): self
    {
        $event->load([
            'map',
            'mode',
        ]);

        return new self(
            id:   $event->ext_id,
            map:  $event->map->name,
            mode: $event->mode->name,
        );
    }
}
