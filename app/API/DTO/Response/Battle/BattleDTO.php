<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;

final readonly class BattleDTO
{
    /**
     * @param string          $battleTime
     * @param BattleEventDTO  $event
     * @param BattleResultDTO $battle
     */
    private function __construct(
        public string          $battleTime,
        public BattleEventDTO  $event,
        public BattleResultDTO $battle,
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
        if (!(isset($data['battleTime']) && is_string($data['battleTime']) && !empty(trim($data['battleTime'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'battleTime' field in player's battle log data."
            );
        }

        if (!(isset($data['event']) && is_array($data['event']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'event' field in player's battle log data."
            );
        }

        if (!(isset($data['battle']) && is_array($data['battle']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'battle' field in player's battle log data."
            );
        }

        return new self(
            battleTime: $data['battleTime'],
            event:      BattleEventDTO::fromArray($data['event']),
            battle:     BattleResultDTO::fromArray($data['battle']),
        );
    }

    /**
     * Factory method to create an array of DTO.
     *
     * @param array<array> $list
     * @return array<self>
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromArrayList(array $list): array
    {
        return array_map(fn(array $item) => self::fromArray($item), $list);
    }
}
