<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;
use JsonException;

final readonly class BattleLogDTO
{
    /**
     * @param BattleDTO[] $battles
     */
    private function __construct(
        public array $battles,
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
            battles: BattleDTO::fromArrayList($data),
        );
    }

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array<array>
     */
    public function jsonSerialize(): array
    {
        $battles = [];

        foreach ($this->battles as $battle) {
            $battles[] = $battle->jsonSerialize();
        }

        return $battles;
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
}
