<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\ClubMemberDTO;
use App\Models\Club;
use App\Models\Player;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ClubRepository implements ClubRepositoryInterface
{
    public function __construct(private PlayerRepositoryInterface $playerRepository) {}

    public function findClub(array $searchCriteria): ?Club
    {
        $query = Club::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['tag'])) {
            $query->where('tag', '=', $searchCriteria['tag']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', '=', $searchCriteria['name']);
        }

        return $query->first();
    }

    public function createOrUpdateClubFromArray(array $attributes): Club
    {
        $validated = self::validateClubData(attributes: $attributes);

        return $this->createOrUpdateClubFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdateClubFromDTO(ClubDTO $clubDTO): Club
    {
        $attributes = [
            'tag'               => $clubDTO->tag,
            'name'              => $clubDTO->name,
            'description'       => $clubDTO->description,
            'type'              => $clubDTO->type,
            'badge_id'          => $clubDTO->badgeId,
            'required_trophies' => $clubDTO->requiredTrophies,
            'trophies'          => $clubDTO->trophies,
        ];

        return $this->createOrUpdateClubFromArray($attributes);
    }

    public function createOrUpdateClubFromTag(string $tag): Club
    {
        $validated = self::validateClubTagData(attributes: ['tag' => $tag]);

        return $this->createOrUpdateClubFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdateClubFromDTOAndSyncClubMembers(ClubDTO $clubDTO): Club
    {
        DB::transaction(function () use (&$club, $clubDTO) {
            $club = $this->createOrUpdateClubFromDTO(clubDTO: $clubDTO);
            $club = $this->syncClubMembers(
                club: $club,
                memberDTOs: $clubDTO->members,
            );

            $club->load(['members']);
        });

        return $club;
    }

    public function createOrUpdateClubFromTagAndSyncClubMembers(string $tag, array $memberDTOs): Club
    {
        $club = null;

        DB::transaction(function () use (&$club, $tag, $memberDTOs) {
            $club = $this->createOrUpdateClubFromTag(tag: $tag);
            $club = $this->syncClubMembers(
                club: $club,
                memberDTOs: $memberDTOs,
            );

            $club->load(['members']);
        });

        if (!$club) {
            throw ValidationException::withMessages(["Club {$tag} has not been created."]);
        }

        return $club;
    }

    public function syncClubMembers(Club $club, array $memberDTOs): Club
    {
        DB::transaction(function () use (&$club, $memberDTOs) {
            $playerIds = [];

            foreach ($memberDTOs as $memberDTO) {
                $player = $this->createOrUpdateClubMember(
                    clubId: $club->id,
                    memberDTO: $memberDTO,
                );

                $playerIds[] = $player->id;
            }

            $this->detachClubMembers(
                clubId: $club->id,
                exceptPlayerIds: $playerIds,
            );
        });

        $club->load(['members']);

        return $club;
    }

    public function createOrUpdateClubMember(int $clubId, ClubMemberDTO $memberDTO): Player
    {
        $clubRawAttributes = [
            'club_id'   => $clubId,
            'club_role' => $memberDTO->role,
        ];
        $clubValidatedAttributes = self::validateClubMemberData(attributes: $clubRawAttributes);
        $playerRawAttributes = [
            'tag'        => $memberDTO->tag,
            'name'       => $memberDTO->name,
            'name_color' => $memberDTO->nameColor,
            'trophies'   => $memberDTO->trophies,
            'icon_id'    => $memberDTO->icon['id'],
        ];
        $playerAttributes = array_merge($playerRawAttributes, $clubValidatedAttributes);

        return $this->playerRepository->createOrUpdatePlayerFromArray(attributes: $playerAttributes);
    }

    public function detachClubMembers(int $clubId, array $exceptPlayerIds): int
    {
        // todo optimize SQL query
        return Club::query()
            ->join('players', function (JoinClause $join) use ($clubId) {
                $join->on('players.club_id', '=', 'clubs.id')
                    ->where('club_id', '=', $clubId);
            })
            ->where('clubs.id', '=', $clubId)
            ->whereNotIn('players.id', $exceptPlayerIds)
            ->update([
                'club_id'   => null,
                'club_role' => null,
            ]);
    }

    private function createOrUpdateClubFromValidatedArray(array $attributes): Club
    {
        $club = $this->findClub([
            'tag' => $attributes['tag'],
        ]);

        if ($club) {
            if (sizeof($attributes) > 1) {
                $club->update(attributes: $attributes);
            }
        } else {
            $club = Club::query()->create(attributes: $attributes);
        }

        return $club;
    }

    /**
     * @param array $attributes
     * @return array
     * @throws ValidationException
     */
    private function validateClubData(array $attributes): array
    {
        $rules = self::getClubRules();

        return Validator::make($attributes, $rules)->validated();
    }

    /**
     * @param array $attributes
     * @return array
     * @throws ValidationException
     */
    private function validateClubTagData(array $attributes): array
    {
        $rules = self::getClubTagRules();

        return Validator::make($attributes, $rules)->validated();
    }

    /**
     * @param array $attributes
     * @return array
     * @throws ValidationException
     */
    private function validateClubMemberData(array $attributes): array
    {
        $rules = self::getClubMemberRules();

        return Validator::make($attributes, $rules)->validated();
    }

    public static function getClubRules(): array
    {
        $tagRules = self::getClubTagRules();
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                'string',
                'max:255',
            ],
            'badge_id' => [
                'required',
                'integer',
            ],
            'required_trophies' => [
                'required',
                'integer',
            ],
            'trophies' => [
                'required',
                'integer',
            ],
            'members' => [
//                'required', // todo this shit just do not work as expected
                'array',
            ],
            'members.*' => [
                'array',
            ],
            'members.*.tag' => [
                'required',
                'string',
                'regex:/^#[A-Z0-9]+$/', // todo move to rule
            ],
            'members.*.name' => [
                'required',
                'string',
                'max:255',
            ],
            'members.*.name_color' => [
                'required',
                'string',
                'regex:/^#[A-Fa-f0-9]{6}$/', // todo valid hex color
            ],
            'members.*.role' => [
                'required',
                'string',
                Rule::in(Club::CLUB_MEMBER_ROLES),
            ],
            'members.*.trophies' => [
                'required',
                'integer',
                'min:0',
            ],
            'members.*.icon' => [
                'required',
                'array',
            ],
            'members.*.icon.id' => [
                'required',
                'integer',
                'min:1',
            ],
        ];

        return array_merge($rules, $tagRules);
    }

    public static function getClubTagRules(): array
    {
        return [
            'tag' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    public static function getClubMemberRules(): array
    {
        return [
            'club_id' => [
                'required_with:club_role',
                'integer',
                'min:1',
                'exists:clubs,id',
            ],
            'club_role' => [
                'required_with:club_id',
                'string',
                'max:255',
                Rule::in(Club::CLUB_MEMBER_ROLES),
            ],
        ];
    }
}
