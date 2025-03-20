<?php

declare(strict_types=1);

namespace App\Services\Repositories;

use App\API\DTO\Response\PlayerBrawlerDTO;
use App\API\DTO\Response\PlayerBrawlerGearDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Accessory;
use App\Models\Brawler;
use App\Models\Club;
use App\Models\Gear;
use App\Models\Player;
use App\Models\PlayerBrawler;
use App\Models\StarPower;
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
    public const PLAYER_RELATIONS = [
        'brawlers',
        'club',
    ];

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

    public function createOrUpdatePlayerFromDataArray(array $attributes): Player
    {
        $validated = self::validatePlayerData(attributes: $attributes);

        return $this->createOrUpdatePlayerFromValidatedArray(attributes: $validated);
    }

    public function createOrUpdatePlayerFromDTO(PlayerDTO $playerDTO): Player
    {
        $validated = [
            'tag'                                   => $playerDTO->tag,
            'name'                                  => $playerDTO->name,
            'name_color'                            => $playerDTO->nameColor,
            'icon_id'                               => $playerDTO->icon['id'],
            'trophies'                              => $playerDTO->trophies,
            'highest_trophies'                      => $playerDTO->highestTrophies,
            'exp_level'                             => $playerDTO->expLevel,
            'exp_points'                            => $playerDTO->expPoints,
            'is_qualified_from_championship_league' => $playerDTO->isQualifiedFromChampionshipChallenge,
            'solo_victories'                        => $playerDTO->victoriesSolo,
            'duo_victories'                         => $playerDTO->victoriesDuo,
            'trio_victories'                        => $playerDTO->victories3vs3,
            'best_time_robo_rumble'                 => $playerDTO->bestRoboRumbleTime,
            'best_time_as_big_brawler'              => $playerDTO->bestTimeAsBigBrawler,
        ];

        return $this->createOrUpdatePlayerFromDataArray(attributes: $validated);
    }

    public function createOrUpdatePlayerFromDTOAndSyncRelations(PlayerDTO $playerDTO): Player
    {
        $player = null;

        DB::transaction(function () use (&$player, $playerDTO) {
            $player = $this->createOrUpdatePlayerFromDTO(playerDTO: $playerDTO);
            $this->syncPlayerRelations(
                player: $player,
                playerBrawlerDTOs: $playerDTO->playerBrawlers,
                clubDataArray: $playerDTO->club,
            );
        });

        if (!$player) {
            // todo try/catch here and continue with actual player (not null)
            throw ValidationException::withMessages(["Player with tag $playerDTO->tag has not been created from DTO: {$playerDTO->toJson()}."]);
        }

        $player->refresh();
        $player->load(self::PLAYER_RELATIONS);

        return $player;
    }

    public function syncPlayerRelations(Player $player, array $playerBrawlerDTOs = [], array $clubDataArray = []): void
    {
        DB::transaction(function () use (&$player, $playerBrawlerDTOs, $clubDataArray) {
            $this->syncPlayerClub(
                player: $player,
                clubDataArray: $clubDataArray,
            );
            $player->load('club');
            $this->syncPlayerBrawlers(
                player: $player,
                playerBrawlerDTOs: $playerBrawlerDTOs,
            );
        });
    }

    public function syncPlayerClub(Player $player, array $clubDataArray = []): bool
    {
        if (empty($clubDataArray)) {
            $player->club_id = null;
            $player->club_role = null;
        } else {
            $clubRepository = app(ClubRepositoryInterface::class);
            $club = $clubRepository->createOrUpdateClubFromArray(attributes: [
                'tag'  => $clubDataArray['tag'],
                'name' => $clubDataArray['name'],
            ]);

            $player->club_id = $club->id;
            // todo at this moment player has no actual 'club_role' (that can be fetched from Club API)
            // Either way we need to actualise the player's role:
            // - in the new club, if club has been changed; or
            // - in the same (old/previous) club, anyway.

            $isSameClub = ($player->club_id === $club->id);

            // club role could be changed
            $player->club_role = $isSameClub ? $player->club_role : null;
//            $player->club_role = null;
        }

        return $player->save();
    }

    /**
     * 1. Sync the list of player brawlers. Create new or update existing PlayerBrawlers. Then sync.
     * For each player brawler:
     * 2. Find or create Brawler
     * 3. Find or create Accessories, Gears, Star Powers for Brawler
     * 4. Attach Brawler to Accessories, Gears, Star Powers. Without sync.
     * 5. Attach Brawler to Player. Find or create PlayerBrawler. Save properties (power, rank, trophies, highestTrophies)
     * 6. Create or update PlayerBrawlerAccessory, PlayerBrawlerGear, PlayerBrawlerStarPower for PlayerBrawler
     * 7. Sync PlayerBrawlerAccessory, PlayerBrawlerGear, PlayerBrawlerStarPower for PlayerBrawler.
     *
     * @param Player $player
     * @param PlayerBrawlerDTO[] $playerBrawlerDTOs
     * @return void
     */
    public function syncPlayerBrawlers(Player $player, array $playerBrawlerDTOs): void
    {
        DB::transaction(function () use (&$player, $playerBrawlerDTOs) {
            $brawlerRepository = app(BrawlerRepositoryInterface::class);
            $accessoryRepository = app(AccessoryRepositoryInterface::class);
            $gearRepository = app(GearRepositoryInterface::class);
            $starPowerRepository = app(StarPowerRepositoryInterface::class);

            $brawlers = collect();

            foreach ($playerBrawlerDTOs as $playerBrawlerDTO) {
                // 1. Find or create Brawler
                $brawler = $brawlerRepository->createOrUpdateBrawlerFromDataArray([
                    'ext_id' => $playerBrawlerDTO->extId,
                    'name'   => $playerBrawlerDTO->name,
                ]);

                $accessories = collect();
                $gears = collect();
                $starPowers = collect();

                // 2.1. Find or create Accessories for Brawler
                foreach ($playerBrawlerDTO->accessories as $playerBrawlerAccessoryDTO) {
                    $accessory = $accessoryRepository->createOrUpdateAccessoryFromDataArray(
                        [
                            'ext_id' => $playerBrawlerAccessoryDTO->extId,
                            'name'   => $playerBrawlerAccessoryDTO->name,
                        ]
                    );

                    $accessories->add($accessory);
                }

                // 2.2. Find or create Gears for Brawler
                foreach ($playerBrawlerDTO->gears as $playerBrawlerGearDTO) {
                    $gear = $gearRepository->createOrUpdateGearFromDataArray(
                        [
                            'ext_id' => $playerBrawlerGearDTO->extId,
                            'name'   => $playerBrawlerGearDTO->name,
                        ]
                    );

                    $gears->add($gear);
                }

                // 2.3. Find or create Star Powers for Brawler
                foreach ($playerBrawlerDTO->starPowers as $playerBrawlerStarPowerDTO) {
                    $starPower = $starPowerRepository->createOrUpdateStarPowerFromDataArray(
                        [
                            'ext_id' => $playerBrawlerStarPowerDTO->extId,
                            'name'   => $playerBrawlerStarPowerDTO->name,
                        ]
                    );

                    $starPowers->add($starPower);
                }

                // 3. Attach Brawler to Accessories, Gears, Star Powers
                $brawler->accessories()->saveMany($accessories);
                $brawler->gears()->saveMany($gears);
                $brawler->starPowers()->saveMany($starPowers);
                $brawler->save();

                $brawlers->add($brawler);

                // 4. Attach Brawler to Player. Save properties (power, rank, trophies, highestTrophies)
                $brawler = $player->brawlers()->save($brawler, [
                    'power'            => $playerBrawlerDTO->power,
                    'rank'             => $playerBrawlerDTO->rank,
                    'trophies'         => $playerBrawlerDTO->trophies,
                    'highest_trophies' => $playerBrawlerDTO->highestTrophies,
                ]);

                $brawler->load([
                    'accessories',
                    'gears',
                    'starPowers',
                    'players',
                ]);

                /** @var PlayerBrawler $playerBrawler */
                $playerBrawler = $brawler->players->where('id', $player->id)->first()->player_brawler;

                // 5. Sync relations with accessories, gears and star powers for PlayerBrawler model
                // 5.1. Detach PlayerBrawler's old relations
                $playerBrawler->playerBrawlerAccessories()->delete();
                $playerBrawler->playerBrawlerGears()->delete();
                $playerBrawler->playerBrawlerStarPowers()->delete();
                $playerBrawler->save();

                // 5.2. Attach PlayerBrawler's new relations: create pivots with properties

                /** @var Accessory $accessoryForLoop */
                foreach ($accessories as $accessoryForLoop) {
                    /** @var Accessory $accessoryWithPivot */
                    $accessoryWithPivot = $brawler->accessories->where('id', $accessoryForLoop->id)->first();

                    // create the relation between PlayerBrawler and brawler_accessory
                    $playerBrawler->playerBrawlerAccessories()->create([
                        'brawler_accessory_id' => $accessoryWithPivot->brawler_accessory->id,
                    ]);
                }

                /** @var Gear $gearForLoop */
                foreach ($gears as $gearForLoop) {
                    /** @var Gear $gearWithPivot */
                    $gearWithPivot = $brawler->gears->where('id', $gearForLoop->id)->first();

                    /** @var PlayerBrawlerGearDTO $playerBrawlerGearDTO */
                    $playerBrawlerGearDTO = collect($playerBrawlerDTO->gears)->first(
                        fn (PlayerBrawlerGearDTO $gearDTO) => (($gearDTO->extId === $gearForLoop->ext_id) && ($gearDTO->name === $gearForLoop->name))
                    );

                    // create the relation between PlayerBrawler and brawler_gear
                    $playerBrawler->playerBrawlerGears()->create([
                        'brawler_gear_id' => $gearWithPivot->brawler_gear->id,
                        'level' => $playerBrawlerGearDTO->level,
                    ]);
                }

                /** @var StarPower $starPowerForLoop */
                foreach ($starPowers as $starPowerForLoop) {
                    /** @var StarPower $starPowerWithPivot */
                    $starPowerWithPivot = $brawler->starPowers->where('id', $starPowerForLoop->id)->first();

                    // create the relation between PlayerBrawler and brawler_star_power
                    $playerBrawler->playerBrawlerStarPowers()->create([
                        'brawler_star_power_id' => $starPowerWithPivot->brawler_star_power->id,
                    ]);
                }

                $playerBrawler->save();
                $playerBrawler->load([
                    'playerBrawlerAccessories',
                    'playerBrawlerGears',
                    'playerBrawlerStarPowers',
                ]);
            }

            $player->save();
            $player->load([
                'brawlers',
            ]);

            // Detach old brawlers: remove old PlayerBrawler pivots with related pivots
            $brawlerIdsToKeep = $brawlers->pluck('id');
            $playerBrawlersToDetach = $player->brawlers->filter(fn(Brawler $brawler) => $brawlerIdsToKeep->doesntContain($brawler->id));

            /** @var Brawler $brawlerToDetach */
            foreach ($playerBrawlersToDetach as $brawlerToDetach) {

                /** @var PlayerBrawler $playerBrawlerToDetach */
                $playerBrawlerToDetach = $brawlerToDetach->player_brawler;

                // nice todo this can be done within handler (observer) of PlayerBrawler's "delete" event
                $playerBrawlerToDetach->playerBrawlerAccessories()->delete();
                $playerBrawlerToDetach->playerBrawlerGears()->delete();
                $playerBrawlerToDetach->playerBrawlerStarPowers()->delete();
                $playerBrawlerToDetach->delete();
            }

            $player->save();
            $player->load([
                'brawlers',
            ]);
        });
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
