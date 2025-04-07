<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;

final readonly class BattlePlayerDTO
{
    /**
     * @param string                        $tag
     * @param string                        $name
     * @param BattlePlayerBrawlerDTO|null   $brawler
     * @param BattlePlayerBrawlerDTO[]|null $brawlers
     */
    private function __construct(
        public string                  $tag,
        public string                  $name,
        public ?BattlePlayerBrawlerDTO $brawler,
        public ?array                  $brawlers,
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
                "Invalid or missing 'tag' field in player's battle data."
            );
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'name' field in player's battle data."
            );
        }

        if (key_exists('brawler', $data) && !is_array($data['brawler'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'brawler' field in player's battle data."
            );
        }

        if (key_exists('brawlers', $data) && !is_array($data['brawlers'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'brawlers' field in player's battle data."
            );
        }

        return new self(
            tag:      $data['tag'],
            name:     $data['name'],
            brawler:  isset($data['brawler']) ? BattlePlayerBrawlerDTO::fromArray($data['brawler']) : null,
            brawlers: isset($data['brawlers']) ? BattlePlayerBrawlerDTO::fromArrayList($data['brawlers']) : null,
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
        $array = [
            'tag'      => $this->tag,
            'name'     => $this->name,
            'brawler'  => $this->brawler?->jsonSerialize(),
            'brawlers' => $this->brawlers ? array_map(fn(BattlePlayerBrawlerDTO $player) => $player->jsonSerialize(), $this->brawlers) : null,
        ];

        return array_filter(
            $array,
            static fn($value) => $value !== null
        );
    }
}
