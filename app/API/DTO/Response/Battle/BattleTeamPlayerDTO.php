<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;

final readonly class BattleTeamPlayerDTO
{
    /**
     * @param string                                                  $tag
     * @param string                                                  $name
     * @param array{id: int, name: string, power: int, trophies: int} $brawler
     */
    private function __construct(
        public string $tag,
        public string $name,
        public array  $brawler,
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
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'tag' field in battle player's data."
            );
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'name' field in battle player's data."
            );
        }

        if (!(isset($data['brawler']) && is_array($data['brawler']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'brawler' field in battle player's data."
            );
        }

        $brawlerData = $data['brawler'];

        if (!(isset($brawlerData['id']) && is_numeric($brawlerData['id']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'id' field in battle player brawler's data."
            );
        }

        if (!(isset($brawlerData['name']) && is_string($brawlerData['name']) && !empty(trim($brawlerData['name'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'name' field in battle player brawler's data."
            );
        }

        if (!(isset($brawlerData['power']) && is_numeric($brawlerData['power']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'id' field in battle player brawler's data."
            );
        }

        if (!(isset($brawlerData['trophies']) && is_numeric($brawlerData['trophies']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'id' field in battle player brawler's data."
            );
        }

        return new self(
            tag:     $data['tag'],
            name:    $data['name'],
            brawler: $data['brawler'],
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
