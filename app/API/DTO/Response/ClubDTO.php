<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Club;
use App\Models\Player;

final readonly class ClubDTO
{
    /**
     * @param string $tag
     * @param string $name
     * @param string $description
     * @param string $type
     * @param int $badgeId
     * @param int $requiredTrophies
     * @param int $trophies
     * @param ClubPlayerDTO[] $members
     */
    private function __construct(
        public string $tag,
        public string $name,
        public string $description,
        public string $type,
        public int $badgeId,
        public int $requiredTrophies,
        public int $trophies,
        public array $members,
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
        if (!(isset($data['tag']) && is_string($data['tag']) && !empty(trim($data['tag'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'tag' field in club data");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in club data");
        }

        if (!(isset($data['description']) && is_string($data['description']) && !empty(trim($data['description'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'description' field in club data");
        }

        if (!(isset($data['type']) && is_string($data['type']) && !empty(trim($data['type'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'type' field in club data");
        }

        if (!(isset($data['badgeId']) && is_numeric($data['badgeId']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'badgeId' field in club data");
        }

        if (!(isset($data['requiredTrophies']) && is_numeric($data['requiredTrophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'requiredTrophies' field in club data");
        }

        if (!(isset($data['trophies']) && is_numeric($data['trophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'trophies' field in club data");
        }

        if (!(isset($data['members']) && is_array($data['members']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'members' field in club data");
        }

        return new self(
            tag: $data['tag'],
            name: $data['name'],
            description: $data['description'],
            type: $data['type'],
            badgeId: (int) $data['badgeId'],
            requiredTrophies: (int) $data['requiredTrophies'],
            trophies: (int) $data['trophies'],
            members: ClubPlayerDTO::fromList($data['members']),
        );
    }

    public static function fromEloquentModel(Club $club): self
    {
        return new self(
            tag: $club->tag,
            name: $club->name,
            description: $club->description,
            type: $club->type,
            badgeId: $club->badge_id,
            requiredTrophies: $club->required_trophies,
            trophies: $club->trophies,
            members: array_map(
                fn(Player $player) => ClubPlayerDTO::fromEloquentModel(player: $player)->toArray(),
                $club->members->all()
            ),
        );
    }
}
