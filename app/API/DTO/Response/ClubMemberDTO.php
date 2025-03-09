<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Player;
use JsonException;

final readonly class ClubMemberDTO
{
    /**
     * @param string $tag
     * @param string $name
     * @param string $nameColor
     * @param string $role
     * @param int $trophies
     * @param array{id: int} $icon
     */
    private function __construct(
        public string $tag,
        public string $name,
        public string $nameColor,
        public string $role,
        public int    $trophies,
        public array  $icon,
    ) {}

    /**
     * @return array{tag: string, name: string, nameColor: string, role: string, trophies: int, icon: array{id: int}}
     */
    public function toArray(): array
    {
        return [
            'tag'       => $this->tag,
            'name'      => $this->name,
            'nameColor' => $this->nameColor,
            'role'      => $this->role,
            'trophies'  => $this->trophies,
            'icon'      => $this->icon,
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
    public static function fromArray(array $data): self
    {
        if (!(isset($data['tag']) && is_string($data['tag']) && !empty(trim($data['tag'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'tag' field in club member data.");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in club member data.");
        }

        if (!(isset($data['nameColor']) && is_string($data['nameColor']) && !empty(trim($data['nameColor'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'nameColor' field in club member data.");
        }

        if (!(isset($data['role']) && is_string($data['role']) && !empty(trim($data['role'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'role' field in club member data.");
        }

        if (!(isset($data['trophies']) && is_numeric($data['trophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'trophies' field in club member data.");
        }

        if (!(isset($data['icon']['id']) && is_numeric($data['icon']['id']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'icon' field in club member data.");
        }

        return new self(
            tag: $data['tag'],
            name: $data['name'],
            nameColor: $data['nameColor'],
            role: $data['role'],
            trophies: (int) $data['trophies'],
            icon: [
                'id' => (int) $data['icon']['id'],
            ],
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
     * @param Player $player
     * @return self
     * @throws InvalidDTOException
     */
    public static function fromEloquentModel(Player $player): self
    {
        if (!$player->club_role) {
            throw InvalidDTOException::fromMessage("Role is required for club member: {$player->toJson()}.");
        }

        return new self(
            tag: $player->tag,
            name: $player->name,
            nameColor: $player->name_color,
            role: $player->club_role,
            trophies: $player->trophies,
            icon: [
                'id' => $player->icon_id,
            ],
        );
    }
}
