<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Club;
use App\Models\Player;
use JsonException;
use TypeError;

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
     * @param ClubMemberDTO[] $members
     */
    private function __construct(
        public string $tag,
        public string $name,
        public string $description,
        public string $type,
        public int    $badgeId,
        public int    $requiredTrophies,
        public int    $trophies,
        public array  $members,
    ) {}

    /**
     * @return array{tag: string, name: string, description: string, type: string, badgeId: int, requiredTrophies: int, trophies: int, members: array<array{tag: string, name: string, nameColor: string, role: string, trophies: int, icon: array{id: int}}>}
     */
    public function toArray(): array
    {
        return [
            'tag'              => $this->tag,
            'name'             => $this->name,
            'description'      => $this->description,
            'type'             => $this->type,
            'badgeId'          => $this->badgeId,
            'requiredTrophies' => $this->requiredTrophies,
            'trophies'         => $this->trophies,
            'members'          => array_map(fn(ClubMemberDTO $member) => $member->toArray(), $this->members),
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
        if (!(isset($data['tag']) && is_string($data['tag']) && !empty(trim($data['tag'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'tag' field in club data.");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in club data.");
        }

        if (!(isset($data['description']) && is_string($data['description']) && !empty(trim($data['description'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'description' field in club data.");
        }

        if (!(isset($data['type']) && is_string($data['type']) && !empty(trim($data['type'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'type' field in club data.");
        }

        if (!(isset($data['badgeId']) && is_numeric($data['badgeId']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'badgeId' field in club data.");
        }

        if (!(isset($data['requiredTrophies']) && is_numeric($data['requiredTrophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'requiredTrophies' field in club data.");
        }

        if (!(isset($data['trophies']) && is_numeric($data['trophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'trophies' field in club data.");
        }

        if (!(isset($data['members']) && is_array($data['members']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'members' field in club data.");
        }

        return new self(
            tag: $data['tag'],
            name: $data['name'],
            description: $data['description'],
            type: $data['type'],
            badgeId: (int) $data['badgeId'],
            requiredTrophies: (int) $data['requiredTrophies'],
            trophies: (int) $data['trophies'],
            members: ClubMemberDTO::fromArrayList($data['members']),
        );
    }

    /**
     * @param Club $club
     * @return self
     * @throws InvalidDTOException
     */
    public static function fromEloquentModel(Club $club): self
    {
        $club->load('members');

        try {
            return new self(
                tag: $club->tag,
                name: $club->name,
                description: $club->description,
                type: $club->type,
                badgeId: $club->badge_id,
                requiredTrophies: $club->required_trophies,
                trophies: $club->trophies,
                members: $club->members->transform(fn(Player $member) => ClubMemberDTO::fromEloquentModel($member))->toArray(),
            );
        } catch (TypeError $exception) {
            throw InvalidDTOException::fromMessage($exception->getMessage());
        }
    }
}
