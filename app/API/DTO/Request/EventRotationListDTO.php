<?php

declare(strict_types=1);

namespace App\API\DTO\Request;

use App\Models\EventRotation;
use JsonException;
use JsonSerializable;

final readonly class EventRotationListDTO implements JsonSerializable
{
    /**
     * @param EventRotation[] $rotations
     */
    private function __construct(public array $rotations)
    {}

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $rotations = [];

        foreach ($this->rotations as $rotation) {
            $rotations[] = EventRotationDTO::fromEloquentModel($rotation)->jsonSerialize();
        }

        return $rotations;
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

    /**
     * @param EventRotation[] $rotations
     * @return self
     */
    public static function fromListOfEloquentModels(array $rotations): self
    {
        return new self($rotations);
    }
}
