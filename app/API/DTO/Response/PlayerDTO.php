<?php

declare(strict_types=1);

namespace App\API\DTO\Response;

use App\API\Exceptions\InvalidDTOException;
use App\Models\Brawler;
use App\Models\Player;
use JsonException;
use TypeError;

final readonly class PlayerDTO
{
    /**
     * @param string $tag
     * @param string $name
     * @param string $nameColor
     * @param array{id: int} $icon
     * @param int $trophies
     * @param int $highestTrophies
     * @param int $expLevel
     * @param int $expPoints
     * @param bool $isQualifiedFromChampionshipChallenge
     * @param int $victoriesSolo
     * @param int $victoriesDuo
     * @param int $victories3vs3
     * @param int $bestRoboRumbleTime
     * @param int $bestTimeAsBigBrawler
     * @param array{tag: string, name: string}|array{} $club is either empty array (if player does not belong to any club), or has required 'tag', 'name' keys.
     * @param PlayerBrawlerDTO[]|array{} $playerBrawlers
     */
    private function __construct(
        public string $tag,
        public string $name,
        public string $nameColor,
        public array  $icon,
        public int    $trophies,
        public int    $highestTrophies,
        public int    $expLevel,
        public int    $expPoints,
        public bool   $isQualifiedFromChampionshipChallenge,
        public int    $victoriesSolo,
        public int    $victoriesDuo,
        public int    $victories3vs3,
        public int    $bestRoboRumbleTime,
        public int    $bestTimeAsBigBrawler,
        public array  $club,
        public array  $playerBrawlers,
    ) {}

    /**
     * @return array{tag: string, name: string, nameColor: string, icon: array{id: int}, trophies: int, highestTrophies: int, expLevel: int, expPoints: int, isQualifiedFromChampionshipChallenge: bool, victoriesSolo: int, victoriesDuo: int, victories3vs3: int, bestRoboRumbleTime: int, bestTimeAsBigBrawler: int, role: string, club: array{tag: string, name: string}|array{}, brawlers: array{array{extId: int, name: string, power: int, rank: int, trophies: int, highestTrophies: int, gadgets: array<array{extId: string, name: string}>, gears: array<array{extId: string, name: string}>, starPowers: array<array{extId: string, name: string}>}}|array{}}
     */
    public function toArray(): array
    {
        return [
            'tag'                                  => $this->tag,
            'name'                                 => $this->name,
            'nameColor'                            => $this->nameColor,
            'icon'                                 => $this->icon,
            'trophies'                             => $this->trophies,
            'highestTrophies'                      => $this->highestTrophies,
            'expLevel'                             => $this->expLevel,
            'expPoints'                            => $this->expPoints,
            'isQualifiedFromChampionshipChallenge' => $this->isQualifiedFromChampionshipChallenge,
            'soloVictories'                        => $this->victoriesSolo,
            'duoVictories'                         => $this->victoriesDuo,
            '3vs3Victories'                        => $this->victories3vs3,
            'bestRoboRumbleTime'                   => $this->bestRoboRumbleTime,
            'bestTimeAsBigBrawler'                 => $this->bestTimeAsBigBrawler,
            'club'                                 => $this->club,
            'brawlers' => array_map(fn(PlayerBrawlerDTO $brawlerDTO) => $brawlerDTO->toArray(), $this->playerBrawlers),        ];
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
            throw InvalidDTOException::fromMessage("Invalid or missing 'tag' field in player data.");
        }

        if (!(isset($data['name']) && is_string($data['name']) && !empty(trim($data['name'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'name' field in player data.");
        }

        if (!(isset($data['nameColor']) && is_string($data['nameColor']) && !empty(trim($data['nameColor'])))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'nameColor' field in player data.");
        }

        if (!(isset($data['icon']['id']) && is_numeric($data['icon']['id']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'icon' field in player data.");
        }

        if (!(isset($data['trophies']) && is_numeric($data['trophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'trophies' field in player data.");
        }

        if (!(isset($data['highestTrophies']) && is_numeric($data['highestTrophies']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'highestTrophies' field in player data.");
        }

        if (!(isset($data['expLevel']) && is_numeric($data['expLevel']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'expLevel' field in player data.");
        }

        if (!(isset($data['expPoints']) && is_numeric($data['expPoints']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'expPoints' field in player data.");
        }

        if (!(isset($data['isQualifiedFromChampionshipChallenge']) && is_bool($data['isQualifiedFromChampionshipChallenge']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'isQualifiedFromChampionshipChallenge' field in player data.");
        }

        if (!(isset($data['soloVictories']) && is_numeric($data['soloVictories']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'soloVictories' field in player data.");
        }

        if (!(isset($data['duoVictories']) && is_numeric($data['duoVictories']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'duoVictories' field in player data.");
        }

        if (!(isset($data['3vs3Victories']) && is_numeric($data['3vs3Victories']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing '3vs3Victories' field in player data.");
        }

        if (!(isset($data['bestRoboRumbleTime']) && is_numeric($data['bestRoboRumbleTime']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'bestRoboRumbleTime' field in player data.");
        }

        if (!(isset($data['bestTimeAsBigBrawler']) && is_numeric($data['bestTimeAsBigBrawler']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'bestTimeAsBigBrawler' field in player data.");
        }

        if (!(key_exists('club', $data) && is_array($data['club']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing or missing 'club' field in player data.");
        }

        $clubData = $data['club'];

        if (!empty($clubData)) {
            if (!(isset($clubData['tag']) && is_string($clubData['tag']) && !empty(trim($clubData['tag'])))) {
                throw InvalidDTOException::fromMessage("Invalid or missing 'club.tag' field in player data.");
            }

            if (!(isset($clubData['name']) && is_string($clubData['name']) && !empty(trim($clubData['name'])))) {
                throw InvalidDTOException::fromMessage("Invalid or missing 'club.name' field in player data.");
            }
        }

        if (!(key_exists('brawlers', $data) && is_array($data['brawlers']))) {
            throw InvalidDTOException::fromMessage("Invalid or missing 'brawlers' field in player data.");
        }

        return new self(
            tag: $data['tag'],
            name: $data['name'],
            nameColor: $data['nameColor'],
            icon: [
                'id' => (int) $data['icon']['id'],
            ],
            trophies: (int) $data['trophies'],
            highestTrophies: (int) $data['highestTrophies'],
            expLevel: (int) $data['expLevel'],
            expPoints: (int) $data['expPoints'],
            isQualifiedFromChampionshipChallenge: (bool) $data['isQualifiedFromChampionshipChallenge'],
            victoriesSolo: (int) $data['soloVictories'],
            victoriesDuo: (int) $data['duoVictories'],
            victories3vs3: (int) $data['3vs3Victories'],
            bestRoboRumbleTime: (int) $data['bestRoboRumbleTime'],
            bestTimeAsBigBrawler: (int) $data['bestTimeAsBigBrawler'],
            club: empty($clubData) ? [] : [
                'tag'  => $clubData['tag'],
                'name' => $clubData['name'],
            ],
            playerBrawlers: PlayerBrawlerDTO::fromArrayList($data['brawlers']),
        );
    }

    /**
     * @param Player $player
     * @return self
     * @throw InvalidDTOException
     */
    public static function fromEloquentModel(Player $player): self
    {
        $player->load(['club', 'brawlers']);

        try {
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
                club: $player->club ? [
                    'tag'  => $player->club->tag,
                    'name' => $player->club->name,
                ] : [],
                playerBrawlers: $player->brawlers->transform(fn(Brawler $brawler) => PlayerBrawlerDTO::fromEloquentModel($brawler->player_brawler))->toArray(),
            );
        } catch (TypeError $exception) {
            throw InvalidDTOException::fromMessage($exception->getMessage());
        }
    }
}
