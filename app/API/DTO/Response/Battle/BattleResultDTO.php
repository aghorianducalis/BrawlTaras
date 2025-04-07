<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Battle;

use App\API\Exceptions\InvalidDTOException;

final readonly class BattleResultDTO
{
    /**
     * @param string                 $mode
     * @param string                 $type
     * @param string|null            $result
     * @param int|null               $duration
     * @param int|null               $trophyChange
     * @param int|null               $rank
     * @param BattlePlayerDTO|null   $starPlayer
     * @param BattleTeamDTO[]|null   $teams
     * @param BattlePlayerDTO[]|null $players
     */
    private function __construct(
        public string           $mode,
        public string           $type,
        public ?string          $result,
        public ?int             $duration,
        public ?int             $trophyChange,
        public ?int             $rank,
        public ?BattlePlayerDTO $starPlayer,
        public ?array           $teams,
        public ?array           $players,
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
        $requiredKeys = [
            'mode',
            'type',
        ];
        $optionalKeys = [
            'result',
            'duration',
            'trophyChange',
            'rank',
            'starPlayer',
            'teams',
            'players',
        ];
        $allKeys = array_merge($requiredKeys, $optionalKeys);
        $dataKeys = array_keys($data);

        // todo proper check
        $doRequiredKeysPresent = sizeof(array_diff($requiredKeys, $dataKeys)) === 0;
        $whetherNewKeysWereAdded = sizeof(array_diff($dataKeys, $allKeys)) > 0;
        $whetherNewKeysWereAdded = sizeof(array_diff($allKeys, $dataKeys)) > sizeof($optionalKeys);

        if ($whetherNewKeysWereAdded) {
            throw InvalidDTOException::fromMessage(
                "List of keys in battle result data array was changed." . json_encode($data)
            );
        }

        if (!(isset($data['mode']) && is_string($data['mode']) && !empty(trim($data['mode'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'mode' field in battle result data."
            );
        }

        if (!(isset($data['type']) && is_string($data['type']) && !empty(trim($data['type'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'type' field in battle result data."
            );
        }

        if (key_exists('result', $data) && !(is_string($data['result']) && !empty(trim($data['result'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'result' field in battle result data."
            );
        }

        if (key_exists('duration', $data) && !is_numeric($data['duration'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'duration' field in battle result data."
            );
        }

        if (key_exists('trophyChange', $data) && !is_numeric($data['trophyChange'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'trophyChange' field in battle result data."
            );
        }

        if (key_exists('rank', $data) && !is_numeric($data['rank'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'rank' field in battle result data."
            );
        }

        if (key_exists('starPlayer', $data) && !(is_null($data['starPlayer']) || is_array($data['starPlayer']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'starPlayer' field in battle result data."
            );
        }

        if (key_exists('teams', $data) && !is_array($data['teams'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'teams' field in battle result data."
            );
        }

        if (key_exists('players', $data) && !is_array($data['players'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'players' field in battle result data."
            );
        }

        $starPlayer = isset($data['starPlayer']) ? BattlePlayerDTO::fromArray($data['starPlayer']) : null;
        $teams = isset($data['teams']) ? BattleTeamDTO::fromArrayList($data['teams']) : null;
        $players = isset($data['players']) ? BattlePlayerDTO::fromArrayList($data['players']) : null;

        return new self(
            mode:         $data['mode'],
            type:         $data['type'],
            result:       $data['result'] ?? null,
            duration:     isset($data['duration']) ? (int) $data['duration'] : null,
            trophyChange: isset($data['trophyChange']) ? (int) $data['trophyChange'] : null,
            rank:         isset($data['rank']) ? (int) $data['rank'] : null,
            starPlayer:   $starPlayer,
            teams:        $teams,
            players:      $players,
        );
    }

    /**
     * Converts the DTO to JSON-serializable format.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $array = [
            'mode'         => $this->mode,
            'type'         => $this->type,
            'result'       => $this->result,
            'duration'     => $this->duration,
            'rank'         => $this->rank,
            'trophyChange' => $this->trophyChange,
            'starPlayer'   => $this->starPlayer?->jsonSerialize(),
            'teams'        => $this->teams ? array_map(fn(BattleTeamDTO $team) => $team->jsonSerialize(), $this->teams) : null,
            'players'      => $this->players ? array_map(fn(BattlePlayerDTO $player) => $player->jsonSerialize(), $this->players) : null,
        ];

        return array_filter(
            $array,
            static fn($value) => $value !== null
        );
    }
}
