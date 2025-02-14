<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Player;
use App\Models\PlayerBrawler;
use JsonException;

final readonly class PlayerDTO
{
    /**
     * @param string $tag
     * @param string $name
     * @param string $nameColor
     * @param array{id: int|null} $icon
     * @param int $trophies
     * @param int|null $highestTrophies
     * @param int|null $expLevel
     * @param int|null $expPoints
     * @param bool|null $isQualifiedFromChampionshipChallenge
     * @param int|null $victoriesSolo
     * @param int|null $victoriesDuo
     * @param int|null $victories3vs3
     * @param int|null $bestRoboRumbleTime
     * @param int|null $bestTimeAsBigBrawler
     * @param string|null $clubRole
     * @param array{tag: string, name: string}|null $club
     * @param PlayerBrawlerDTO[]|null $brawlers
     */
    private function __construct(
        public string $tag,
        public string $name,
        public string $nameColor,
        public array $icon,
        public int $trophies,
        public ?int $highestTrophies,
        public ?int $expLevel,
        public ?int $expPoints,
        public ?bool $isQualifiedFromChampionshipChallenge,
        public ?int $victoriesSolo,
        public ?int $victoriesDuo,
        public ?int $victories3vs3,
        public ?int $bestRoboRumbleTime,
        public ?int $bestTimeAsBigBrawler,
        public ?string $clubRole,
        public ?array $club,
        public ?array $brawlers,
    ) {}

    /**
     * @return array{tag: string, name: string, nameColor: string, icon: array{id: int|null}, trophies: int, highestTrophies: int|null, expLevel: int|null, expPoints: int|null, isQualifiedFromChampionshipChallenge: bool|null, victoriesSolo: int|null, victoriesDuo: int|null, victories3vs3: int|null, bestRoboRumbleTime: int|null, bestTimeAsBigBrawler: int|null, role: string|null, club: null|array{tag: string, name: string}, brawlers: null|array{array{extId: int, name: string, power: int, rank: int, trophies: int, highestTrophies: int, gadgets: array<array{extId: string, name: string}>, gears: array<array{extId: string, name: string}>, starPowers: array<array{extId: string, name: string}>}}}
     */
    public function toArray(): array
    {
        $array = [
            'tag' => $this->tag,
            'name' => $this->name,
            'nameColor' => $this->nameColor,
            'icon' => $this->icon,
            'trophies' => $this->trophies,
            'highestTrophies' => $this->highestTrophies,
            'expLevel' => $this->expLevel,
            'expPoints' => $this->expPoints,
            'isQualifiedFromChampionshipChallenge' => $this->isQualifiedFromChampionshipChallenge,
            'soloVictories' => $this->victoriesSolo,
            'duoVictories' => $this->victoriesDuo,
            '3vs3Victories' => $this->victories3vs3,
            'bestRoboRumbleTime' => $this->bestRoboRumbleTime,
            'bestTimeAsBigBrawler' => $this->bestTimeAsBigBrawler,
        ];

        if ($this->clubRole) {
            $array['role'] = $this->clubRole;
        }

        if ($this->club) {
            $array['club'] = [
                'tag' => $this->club['tag'],
                'name' => $this->club['name'],
            ];
        }

        if ($this->brawlers) {
            $array['brawlers'] = array_map(fn(PlayerBrawlerDTO $brawlerDTO) => $brawlerDTO->toArray(), $this->brawlers);
        }

        return array_filter($array, fn($value) => !is_null($value));
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
            throw InvalidDTOException::fromMessage("Invalid or missing 'tag' field in player data.");
        }

        if (!(key_exists('name', $data) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in player data.");
        }

        if (!(key_exists('nameColor', $data) && is_string($data['nameColor']) && !empty(trim($data['nameColor'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'nameColor' field in player data.");
        }

        if (!(isset($data['icon']) && is_array($data['icon']) && key_exists('id', $data['icon']) && (is_null($data['icon']['id']) || is_numeric($data['icon']['id'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'icon' field in player data.");
        }

        if (!(key_exists('trophies', $data) && is_numeric($data['trophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'trophies' field in player data.");
        }

        if (key_exists('highestTrophies', $data) && !is_numeric($data['highestTrophies'])) {
            throw InvalidDTOException::fromMessage("Invalid 'highestTrophies' field in player data.");
        }

        if (key_exists('expLevel', $data) && !is_numeric($data['expLevel'])) {
            throw InvalidDTOException::fromMessage("Invalid 'expLevel' field in player data.");
        }

        if (key_exists('expPoints', $data) && !is_numeric($data['expPoints'])) {
            throw InvalidDTOException::fromMessage("Invalid 'expPoints' field in player data.");
        }

        if (key_exists('isQualifiedFromChampionshipChallenge', $data) && !is_bool($data['isQualifiedFromChampionshipChallenge'])) {
            throw InvalidDTOException::fromMessage("Invalid 'isQualifiedFromChampionshipChallenge' field in player data.");
        }

        if (key_exists('soloVictories', $data) && !is_numeric($data['soloVictories'])) {
            throw InvalidDTOException::fromMessage("Invalid 'soloVictories' field in player data.");
        }

        if (key_exists('duoVictories', $data) && !is_numeric($data['duoVictories'])) {
            throw InvalidDTOException::fromMessage("Invalid 'duoVictories' field in player data.");
        }

        if (key_exists('3vs3Victories', $data) && !is_numeric($data['3vs3Victories'])) {
            throw InvalidDTOException::fromMessage("Invalid '3vs3Victories' field in player data.");
        }

        if (key_exists('bestRoboRumbleTime', $data) && !is_numeric($data['bestRoboRumbleTime'])) {
            throw InvalidDTOException::fromMessage("Invalid 'bestRoboRumbleTime' field in player data.");
        }

        if (key_exists('bestTimeAsBigBrawler', $data) && !is_numeric($data['bestTimeAsBigBrawler'])) {
            throw InvalidDTOException::fromMessage("Invalid 'bestTimeAsBigBrawler' field in player data.");
        }

        if (key_exists('role', $data) && !(is_string($data['role']) && !empty(trim($data['role'])))) {
            throw InvalidDTOException::fromMessage("Invalid 'role' field in player data.");
        }

        /*
         * club data array must:
         * - be empty if player does not belong to any club, or
         * - have not-empty 'tag' and 'name' fields otherwise.
         */
        $club = null;

        if (key_exists('club', $data) && !is_null($data['club'])) {
            $clubData = $data['club'];

            if (!is_array($clubData)) {
                throw InvalidDTOException::fromMessage("Invalid 'club' field in player data.");
            } elseif (!(key_exists('tag', $clubData) && is_string($clubData['tag']) && !empty(trim($clubData['tag'])))) {
                throw InvalidDTOException::fromMessage("Invalid or missing 'club.tag' field in player data.");
            } elseif (!(key_exists('name', $clubData) && is_string($clubData['name']) && !empty(trim($clubData['name'])))) {
                throw InvalidDTOException::fromMessage("Invalid or missing 'club.name' field in player data.");
            }

            $club = [
                'tag' => $clubData['tag'],
                'name' => $clubData['name'],
            ];
        }

        $brawlers = null;

        if (key_exists('brawlers', $data)) {
            if (is_array($data['brawlers'])) {
                $brawlers = array_map(fn(array $brawlerData) => PlayerBrawlerDTO::fromArray($brawlerData), $data['brawlers']);
            } elseif (!is_null($data['brawlers'])) {
                throw InvalidDTOException::fromMessage("Invalid 'brawlers' field in player data.");
            }
        }

        return new self(
            tag: $data['tag'],
            name: $data['name'],
            nameColor: $data['nameColor'],
            icon: [
                'id' => $data['icon']['id'],
            ],
            trophies: (int) $data['trophies'],
            highestTrophies: isset($data['highestTrophies']) ? (int) $data['highestTrophies'] : null,
            expLevel: isset($data['expLevel']) ? (int) $data['expLevel'] : null,
            expPoints: isset($data['expPoints']) ? (int) $data['expPoints'] : null,
            isQualifiedFromChampionshipChallenge: isset($data['isQualifiedFromChampionshipChallenge']) ? (bool) $data['isQualifiedFromChampionshipChallenge'] : null,
            victoriesSolo: isset($data['soloVictories']) ? (int) $data['soloVictories'] : null,
            victoriesDuo: isset($data['duoVictories']) ? (int) $data['duoVictories'] : null,
            victories3vs3: isset($data['3vs3Victories']) ? (int) $data['3vs3Victories'] : null,
            bestRoboRumbleTime: isset($data['bestRoboRumbleTime']) ? (int) $data['bestRoboRumbleTime'] : null,
            bestTimeAsBigBrawler: isset($data['bestTimeAsBigBrawler']) ? (int) $data['bestTimeAsBigBrawler'] : null,
            clubRole: $data['role'] ?? null,
            club: $club,
            brawlers: $brawlers,
        );
    }

    public static function fromEloquentModel(Player $player): self
    {
        return new self(
            tag: $player->tag,
            name: $player->name,
            nameColor: $player->name_color,
            icon: [
                'id' => $player->icon_id,
            ],
            trophies: $player->trophies,
            highestTrophies: $player->highest_trophies,
            expLevel: $player->exp_level,
            expPoints: $player->exp_points,
            isQualifiedFromChampionshipChallenge: $player->is_qualified_from_championship_league,
            victoriesSolo: $player->solo_victories,
            victoriesDuo: $player->duo_victories,
            victories3vs3: $player->trio_victories,
            bestRoboRumbleTime: $player->best_time_robo_rumble,
            bestTimeAsBigBrawler: $player->best_time_as_big_brawler,
            clubRole: $player->club_role,
            club: $player->club ?
                [
                    'tag' => $player->club->tag,
                    'name' => $player->club->name,
                ] :
                null,
            brawlers: $player->playerBrawlers->isEmpty() ?
                null :
                array_map(fn(PlayerBrawler $brawler) => PlayerBrawlerDTO::fromEloquentModel($brawler), $player->playerBrawlers),
        );
    }
}
