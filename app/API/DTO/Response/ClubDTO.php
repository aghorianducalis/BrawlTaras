<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Club;
use App\Models\Player;
use JsonException;

final readonly class ClubDTO
{
    /**
     * @param string $tag
     * @param string|null $name
     * @param string|null $description
     * @param string|null $type
     * @param int|null $badgeId
     * @param int|null $requiredTrophies
     * @param int|null $trophies
     * @param PlayerDTO[]|null $members
     */
    private function __construct(
        public string $tag,
        public ?string $name,
        public ?string $description,
        public ?string $type,
        public ?int $badgeId,
        public ?int $requiredTrophies,
        public ?int $trophies,
        public ?array $members,
    ) {}

    /**
     * @return array{tag: string, name: string|null, description: string|null, type: string|null, badgeId: int|null, requiredTrophies: int|null, trophies: int|null, members: array<array{tag: string, name: string, nameColor: string, role: string, trophies: int, icon: array{id: int}}>|null}
     */
    public function toArray(): array
    {
        return [
            'tag' => $this->tag,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'badgeId' => $this->badgeId,
            'requiredTrophies' => $this->requiredTrophies,
            'trophies' => $this->trophies,
            'members' => is_null($this->members) ? null : array_map(fn(PlayerDTO $member) => [
                'tag' => $member->tag,
                'name' => $member->name,
                'nameColor' => $member->nameColor,
                'role' => $member->clubRole,
                'trophies' => $member->trophies,
                'icon' => $member->icon,
            ], $this->members),
        ];
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Factory method to create DTO.
     *
     * @param array $data
     * @return self
     * @throws InvalidDTOException if required fields are missing or invalid.
     */
    public static function fromDataArray(array $data): self
    {
        if (!(key_exists('tag', $data) && is_string($data['tag']) && !empty(trim($data['tag'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'tag' field in club data.");
        }

        if (key_exists('name', $data) && !(is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid 'name' field in club data.");
        }

        if (key_exists('description', $data) && !(is_string($data['description']) && !empty(trim($data['description'])))) {
            throw InvalidDTOException::fromMessage("Invalid 'description' field in club data.");
        }

        if (key_exists('type', $data) && !(is_string($data['type']) && !empty(trim($data['type'])))) {
            throw InvalidDTOException::fromMessage("Invalid 'type' field in club data.");
        }

        if (key_exists('badgeId', $data) && !is_numeric($data['badgeId'])) {
            throw InvalidDTOException::fromMessage("Invalid 'badgeId' field in club data.");
        }

        if (key_exists('requiredTrophies', $data) && !is_numeric($data['requiredTrophies'])) {
            throw InvalidDTOException::fromMessage("Invalid 'requiredTrophies' field in club data.");
        }

        if (key_exists('trophies', $data) && !is_numeric($data['trophies'])) {
            throw InvalidDTOException::fromMessage("Invalid 'trophies' field in club data.");
        }

        if (key_exists('members', $data) && !is_array($data['members'])) {
            throw InvalidDTOException::fromMessage("Invalid 'members' field in club data.");
        }

        return new self(
            tag: $data['tag'],
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            type: $data['type'] ?? null,
            badgeId: key_exists('badgeId', $data) ? (int) $data['badgeId'] : null,
            requiredTrophies: key_exists('requiredTrophies', $data) ? (int) $data['requiredTrophies'] : null,
            trophies: key_exists('trophies', $data) ? (int) $data['trophies'] : null,
            members: key_exists('members', $data) ? array_map(fn(array $memberData) => PlayerDTO::fromDataArray($memberData), $data['members']) : null,
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
            members: $club->members ? array_map(fn(Player $member) => PlayerDTO::fromEloquentModel($member), $club->members->all()) : null,
        );
    }
}
