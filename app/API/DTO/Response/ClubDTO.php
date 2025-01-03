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
        // Validate the structure of the data array
        if (!(
            isset(
                $data['tag'],
                $data['name'],
                $data['description'],
                $data['type'],// todo enum or model
                $data['badgeId'],
                $data['requiredTrophies'],
                $data['trophies'],
                $data['members'],
            ) &&
            is_numeric($data['badgeId']) &&
            is_numeric($data['requiredTrophies']) &&
            is_numeric($data['trophies']) &&
            is_array($data['members'])
        )) {
            throw InvalidDTOException::fromMessage(
                "Club data array has an invalid structure: " . json_encode($data)
            );
        }

        // Create a new DTO instance
        return new self(
            $data['tag'],
            $data['name'],
            $data['description'],
            $data['type'],
            (int) $data['badgeId'],
            (int) $data['requiredTrophies'],
            (int) $data['trophies'],
            ClubPlayerDTO::fromList($data['members']),
        );
    }

    /**
     * @param Club $club
     * @return self
     */
    public static function fromEloquentModel(Club $club): self
    {
        return self::fromArray(self::eloquentModelToArray(club: $club));
    }

    public static function eloquentModelToArray(Club $club): array
    {
        return [
            'tag' => $club->tag,
            'name' => $club->name,
            'description' => $club->description,
            'type' => $club->type,
            'badgeId' => $club->badge_id,
            'requiredTrophies' => $club->required_trophies,
            'trophies' => $club->trophies,
            'members' => array_map(
                fn(Player $player) => ClubPlayerDTO::eloquentModelToArray(player: $player),
                $club->members->all()
            ),
        ];
    }
}
