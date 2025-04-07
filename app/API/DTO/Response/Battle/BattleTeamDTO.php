<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;

final readonly class BattleTeamDTO
{
    /**
     * @param BattlePlayerDTO[] $teamPlayers
     */
    private function __construct(
        public array $teamPlayers,
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
        return new self(
            teamPlayers: BattlePlayerDTO::fromArrayList($data),
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

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(fn(BattlePlayerDTO $teamPlayer) => $teamPlayer->jsonSerialize(), $this->teamPlayers);
    }
}
