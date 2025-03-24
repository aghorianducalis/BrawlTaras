<?php

declare(strict_types=1);

namespace App\API\DTO\Response\Player\Battle;

use App\API\Exceptions\InvalidDTOException;

final readonly class BattleResultDTO
{
    /**
     * @param string              $mode
     * @param string              $type
     * @param string              $result
     * @param int                 $duration
     * @param BattleStarPlayerDTO $starPlayer
     * @param BattleTeamDTO[]     $teams
     * @param int|null            $trophyChange
     */
    private function __construct(
        public string              $mode,
        public string              $type,
        public string              $result,
        public int                 $duration,
        public BattleStarPlayerDTO $starPlayer,
        public array               $teams,
        public ?int                $trophyChange,
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
        $dataKeys = array_keys($data);
        $requiredKeys = [
            'mode',
            'type',
            'result',
            'duration',
            'starPlayer',
            'teams',
        ];
        $optionalKeys = [
            'trophyChange',
        ];
        $allKeys = array_merge($requiredKeys, $optionalKeys);

        $whetherNewKeysWereAdded = (sizeof(array_diff($allKeys, $dataKeys)) > 1) || (sizeof(array_diff($dataKeys, $allKeys)) > 0);

        if ($whetherNewKeysWereAdded) {
            throw InvalidDTOException::fromMessage(
                "List of keys in battle result data array was changed."
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

        if (!(isset($data['result']) && is_string($data['result']) && !empty(trim($data['result'])))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'result' field in battle result data."
            );
        }

        if (!(isset($data['duration']) && is_numeric($data['duration']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'duration' field in battle result data."
            );
        }

        if (key_exists('trophyChange', $data) && !is_numeric($data['trophyChange'])) {
            throw InvalidDTOException::fromMessage(
                "Invalid 'trophyChange' field in battle result data."
            );
        }

        if (!(isset($data['starPlayer']) && is_array($data['starPlayer']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'starPlayer' field in battle result data."
            );
        }

        if (!(isset($data['teams']) && is_array($data['teams']))) {
            throw InvalidDTOException::fromMessage(
                "Invalid or missing 'teams' field in battle result data."
            );
        }

        return new self(
            mode:         $data['mode'],
            type:         $data['type'],
            result:       $data['result'],
            duration:     (int) $data['duration'],
            starPlayer:   BattleStarPlayerDTO::fromArray($data['starPlayer']),
            teams:        BattleTeamDTO::fromArrayList($data['teams']),
            trophyChange: isset($data['trophyChange']) ? (int) $data['trophyChange'] : null,
        );
    }
}
