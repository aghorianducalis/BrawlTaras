<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\AccessoryDTO;
use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Club;
use App\Models\Player;
use App\Models\PlayerBrawler;
use App\Services\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use App\Services\Repositories\Contracts\ClubRepositoryInterface;
use App\Services\Repositories\Contracts\GearRepositoryInterface;
use App\Services\Repositories\Contracts\PlayerRepositoryInterface;
use App\Services\Repositories\Contracts\StarPowerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class PlayerRepository implements PlayerRepositoryInterface
{
    public function __construct() {}

    public function findPlayer(array $searchCriteria): ?Player
    {
        $query = Player::query();

        if (isset($searchCriteria['id'])) {
            $query->where('id', '=', $searchCriteria['id']);
        }

        if (isset($searchCriteria['tag'])) {
            $query->where('tag', '=', $searchCriteria['tag']);
        }

        if (isset($searchCriteria['name'])) {
            $query->where('name', '=', $searchCriteria['name']);
        }

        if (isset($searchCriteria['club_id'])) {
            $query->where('club_id', '=', $searchCriteria['club_id']);
        }

        if (isset($searchCriteria['club_role'])) {
            $query->where('club_role', '=', $searchCriteria['club_role']);
        }

        return $query->first();
    }

    public function createOrUpdatePlayerFromArray(array $attributes): Player
    {
        $validated = self::validatePlayerData(attributes: $attributes);

        return $this->createOrUpdatePlayerFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdatePlayerFromDTO(PlayerDTO $playerDTO): Player
    {
        $attributes = [
            'tag' => $playerDTO->tag,
            'name' => $playerDTO->name,
            'name_color' => $playerDTO->nameColor,
            'icon_id' => $playerDTO->icon['id'],
            'trophies' => $playerDTO->trophies,
            'highest_trophies' => $playerDTO->highestTrophies,
            'exp_level' => $playerDTO->expLevel,
            'exp_points' => $playerDTO->expPoints,
            'is_qualified_from_championship_league' => $playerDTO->isQualifiedFromChampionshipChallenge,
            'solo_victories' => $playerDTO->victoriesSolo,
            'duo_victories' => $playerDTO->victoriesDuo,
            'trio_victories' => $playerDTO->victories3vs3,
            'best_time_robo_rumble' => $playerDTO->bestRoboRumbleTime,
            'best_time_as_big_brawler' => $playerDTO->bestTimeAsBigBrawler,
        ];

        return $this->createOrUpdatePlayerFromArray($attributes);
    }

    public function createOrUpdatePlayerFromTagAndSyncRelations(string $tag, PlayerDTO $playerDTO): Player
    {
        $player = null;

        DB::transaction(function () use (&$player, $playerDTO) {
            $player = $this->createOrUpdatePlayerFromDTO($playerDTO);

            if (empty($playerDTO->club)) {
                $this->createOrUpdatePlayerFromArray([
                    'tag'       => $playerDTO->tag,
                    'club_id'   => null,
                    'club_role' => null,
                ]);
                // nice todo remove duplicates
                $player->club()->disassociate();
                $player->save();
            } else {
                $clubRepository = app(ClubRepositoryInterface::class);
                $club = $clubRepository->createOrUpdateClubFromTag(tag: $playerDTO->club['tag']);
                $player->club()->associate($club);
                $player->save();
                // todo at this moment player has no actual 'club_role' (that can be fetched from Club API)
            }

            $player = $this->syncPlayerBrawlers($player, $playerDTO->playerBrawlers);
        });

        if (!$player) {
            throw ValidationException::withMessages(["Player {$tag} has not been created."]);
        }

        $player->load(['club', 'brawlers',]);

        return $player;
    }

    public function syncPlayerBrawlers(Player $player, array $playerBrawlerDTOs): Player
    {
        DB::transaction(function () use (&$player, $playerBrawlerDTOs) {
            $brawlerRepository = app(BrawlerRepositoryInterface::class);
            $accessoryRepository = app(AccessoryRepositoryInterface::class);
            $gearRepository = app(GearRepositoryInterface::class);
            $starPowerRepository = app(StarPowerRepositoryInterface::class);

            $brawlers = collect();

            foreach ($playerBrawlerDTOs as $playerBrawlerDTO) {
                $brawler = $brawlerRepository->findBrawler(['ext_id' => $playerBrawlerDTO->extId]);

                if (!$brawler) {
                    $brawler = $brawlerRepository->createOrUpdateBrawler(BrawlerDTO::fromArray([
                        'ext_id' => $playerBrawlerDTO->extId,
                        'name'   => $playerBrawlerDTO->name,
                    ]));
                }

                /** @var PlayerBrawler $playerBrawler */
                $playerBrawler = $player->brawlers()->save($brawler, [
                    'power'            => $playerBrawlerDTO->power,
                    'rank'             => $playerBrawlerDTO->rank,
                    'trophies'         => $playerBrawlerDTO->trophies,
                    'highest_trophies' => $playerBrawlerDTO->highestTrophies,
                ]);

                /** @var PlayerBrawler $actualPlayerBrawler */
                $actualPlayerBrawler = $playerBrawler->player_brawler;

                /*
                 *  sync player brawler accessories
                 */

                $brawler->load([
                    'accessories',
                    'gears',
                    'starPowers',
                ]);

                $accessories = collect();

                foreach ($playerBrawlerDTO->accessories as $playerBrawlerAccessoryDTO) {
                    $accessory = $accessoryRepository->findAccessory(['ext_id' => $playerBrawlerAccessoryDTO->extId]);

                    if (!$accessory) {
                        $accessory = $accessoryRepository->createOrUpdateAccessory(AccessoryDTO::fromArray([
                            'ext_id' => $playerBrawlerAccessoryDTO->extId,
                            'name'   => $playerBrawlerAccessoryDTO->name,
                        ]));
                    }

                    $accessories->add($accessory);

                    // create or update the relation between Brawler and Accessory
//                    $brawler->accessories()->save($accessory);
                    $accessoryWithPivot = $brawler->accessories()->save($accessory);
                    $brawler->save();

                    // or create via PlayerBrawlerAccessory::create()
                    $playerBrawler->playerBrawlerAccessories()->create([
//                    'player_brawler_id'    => $actualPlayerBrawler->id,
//                        'brawler_accessory_id' => $accessoryWithPivot->brawler_accessory->id,
                        'brawler_accessory_id' => $brawler->brawler_accessory->id,
                    ]);
                    $playerBrawler->save();
                    $playerBrawler->refresh();
                    $playerBrawler->load([
                        'playerBrawlerAccessories',
                    ]);
                }

                // Detach old accessories
                $playerBrawler->playerBrawlerAccessories()
                    // todo fix
                    ->whereNotIn('id', $accessories->pluck('id')->toArray())
                    ->delete();
                $playerBrawler->save();
                $playerBrawler->refresh();
                $playerBrawler->load([
                    'playerBrawlerAccessories',
                ]);

                /*
                 *  End of sync player brawler accessories
                 */

                $brawlers->add($brawler);
            }

            // Detach old brawlers
            $player->brawlers()
                ->whereNotIn('id', $brawlers->pluck('id')->toArray())
                ->detach();
        });

        $player->refresh();
        $player->load([
            'brawlers',
        ]);

        return $player;
    }

    private function createOrUpdatePlayerFromValidatedArray(array $attributes): Player
    {
        $player = $this->findPlayer([
            'tag' => $attributes['tag'],
        ]);

        if ($player) {
            $player->update(attributes: $attributes);
        } else {
            $player = Player::query()->create(attributes: $attributes);
        }

        return $player;
    }

    /**
     * @param array $attributes
     * @return array
     * @throws ValidationException
     */
    private static function validatePlayerData(array $attributes): array
    {
        $rules = self::getPlayerRules();

        return Validator::make($attributes, $rules)->validated();
    }

    public static function getPlayerRules(): array
    {
        $rules = [
            'tag' => [
                'required',
                'string',
                'max:255',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'name_color' => [
                'required',
                'string',
                'max:255',
            ],
            'icon_id' => [
                'required',
                'integer',
                'min:0',
            ],
            'trophies' => [
                'required',
                'integer',
                'min:0',
            ],
            'highest_trophies' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'exp_level' => [],
            'exp_points' => [],
            'is_qualified_from_championship_league' => [],
            'solo_victories' => [],
            'duo_victories' => [],
            'trio_victories' => [],
            'best_time_robo_rumble' => [],
            'best_time_as_big_brawler' => [],
            // todo move from club repo
            'club_id' => [
                'nullable',
                'required_with:club_role',
                'integer',
                'min:1',
                'exists:clubs,id',
            ],
            'club_role' => [
                'nullable',
                'required_with:club_id',
                'string',
                'max:255',
                Rule::in(Club::CLUB_MEMBER_ROLES),
            ],
        ];

        return $rules;
    }
}
