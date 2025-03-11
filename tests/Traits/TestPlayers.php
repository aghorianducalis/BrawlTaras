<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\PlayerBrawlerAccessoryDTO;
use App\API\DTO\Response\PlayerBrawlerDTO;
use App\API\DTO\Response\PlayerBrawlerGearDTO;
use App\API\DTO\Response\PlayerBrawlerStarPowerDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Brawler;
use App\Models\Player;
use App\Models\PlayerBrawler;
use App\Models\PlayerBrawlerAccessory;
use App\Models\PlayerBrawlerGear;
use App\Models\PlayerBrawlerStarPower;
use Database\Factories\PlayerFactory;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Player::class)]
#[UsesClass(PlayerBrawler::class)]
#[UsesClass(PlayerFactory::class)]
#[UsesClass(PlayerDTO::class)]
#[UsesClass(PlayerBrawlerDTO::class)]
trait TestPlayers
{
    public final const PLAYER_RELATIONS = [
        'club',
        'brawlers',
    ];

    public final const PLAYER_BRAWLER_RELATIONS = [
        'brawler',
        'playerBrawlerAccessories',
        'playerBrawlerGears',
        'playerBrawlerStarPowers',
    ];

    public function createPlayer(
        array|callable $attributes = [],
    ) : Player {
        return Player::factory()
            ->withClub()
            ->withBrawlers()
            ->create($attributes);
    }

    public function assertPlayerEloquentModelsAreEqual(Player $playerExpected, Player $playerActual): void
    {
        $playerExpected->refresh();
        $playerActual->refresh();
        $playerExpected->load(self::PLAYER_RELATIONS);
        $playerActual->load(self::PLAYER_RELATIONS);

        $this->assertSame($playerExpected->id, $playerActual->id);
        $this->assertSame($playerExpected->tag, $playerActual->tag);
        $this->assertSame($playerExpected->name, $playerActual->name);
        $this->assertSame($playerExpected->name_color, $playerActual->name_color);
        $this->assertSame($playerExpected->icon_id, $playerActual->icon_id);
        $this->assertSame($playerExpected->trophies, $playerActual->trophies);
        $this->assertSame($playerExpected->highest_trophies, $playerActual->highest_trophies);
        $this->assertSame($playerExpected->exp_level, $playerActual->exp_level);
        $this->assertSame($playerExpected->exp_points, $playerActual->exp_points);
        $this->assertSame($playerExpected->is_qualified_from_championship_league, $playerActual->is_qualified_from_championship_league);
        $this->assertSame($playerExpected->solo_victories, $playerActual->solo_victories);
        $this->assertSame($playerExpected->duo_victories, $playerActual->duo_victories);
        $this->assertSame($playerExpected->trio_victories, $playerActual->trio_victories);
        $this->assertSame($playerExpected->best_time_robo_rumble, $playerActual->best_time_robo_rumble);
        $this->assertSame($playerExpected->best_time_as_big_brawler, $playerActual->best_time_as_big_brawler);
        $this->assertSame($playerExpected->club_id, $playerActual->club_id);
        $this->assertSame($playerExpected->club_role, $playerActual->club_role);
        $this->assertTrue($playerExpected->created_at->equalTo($playerActual->created_at));

        $this->assertSame(
            $playerExpected->club?->toArray(),
            $playerActual->club?->toArray()
        );

        $this->assertCount($playerExpected->brawlers->count(), $playerActual->brawlers);

        foreach ($playerActual->brawlers as $i => $brawlerActual) {
            $this->assertInstanceOf(Brawler::class, $brawlerActual);

            /** @var Brawler $brawlerExpected */
            $brawlerExpected = $playerExpected->brawlers->get($i);
            $this->assertInstanceOf(Brawler::class, $brawlerExpected);

            $this->assertPlayerBrawlerModelsAreEqual(
                brawlerExpected: $brawlerExpected,
                brawlerActual: $brawlerActual
            );
        }
    }

    public function assertPlayerBrawlerModelsAreEqual(Brawler $brawlerExpected, Brawler $brawlerActual): void
    {
        $this->assertSame($brawlerExpected->id, $brawlerActual->id);
        $this->assertSame($brawlerExpected->ext_id, $brawlerActual->ext_id);
        $this->assertSame($brawlerExpected->name, $brawlerActual->name);

        /** @var PlayerBrawler $playerBrawlerExpected */
        $playerBrawlerExpected = $brawlerExpected->player_brawler;
        /** @var PlayerBrawler $playerBrawlerActual */
        $playerBrawlerActual = $brawlerActual->player_brawler;
        $this->assertInstanceOf(PlayerBrawler::class, $playerBrawlerExpected);
        $this->assertInstanceOf(PlayerBrawler::class, $playerBrawlerActual);

        $this->assertPlayerBrawlerPivotModelsAreEqual($playerBrawlerExpected, $playerBrawlerActual);
    }

    public function assertPlayerBrawlerPivotModelsAreEqual(PlayerBrawler $playerBrawlerExpected, PlayerBrawler $playerBrawlerActual): void
    {
        // todo
//        dd($playerBrawlerExpected, $playerBrawlerActual);
    }

    /**
     * Covers consistency between Eloquent model and data array.
     *
     * @param Player $player
     * @param array $playerData
     * @param bool $checkRelations
     * @return void
     */
    public function assertPlayerEloquentModelMatchesDataArray(
        Player $player,
        array $playerData,
        bool $checkRelations = true,
    ) : void
    {
        $player->refresh();

        $this->assertArrayHasKey('tag', $playerData);
        $this->assertSame($player->tag, $playerData['tag']);

        $this->assertArrayHasKey('name', $playerData);
        $this->assertSame($player->name, $playerData['name']);

        $this->assertArrayHasKey('name_color', $playerData);
        $this->assertSame($player->name_color, $playerData['name_color']);

        $this->assertArrayHasKey('icon_id', $playerData);
        $this->assertSame($player->icon_id, $playerData['icon_id']);

        $this->assertArrayHasKey('trophies', $playerData);
        $this->assertSame($player->trophies, $playerData['trophies']);

        $this->assertArrayHasKey('highest_trophies', $playerData);
        $this->assertSame($player->highest_trophies, $playerData['highest_trophies']);

        $this->assertArrayHasKey('exp_level', $playerData);
        $this->assertSame($player->exp_level, $playerData['exp_level']);

        $this->assertArrayHasKey('exp_points', $playerData);
        $this->assertSame($player->exp_points, $playerData['exp_points']);

        $this->assertArrayHasKey('is_qualified_from_championship_league', $playerData);
        $this->assertSame($player->is_qualified_from_championship_league, $playerData['is_qualified_from_championship_league']);

        $this->assertArrayHasKey('solo_victories', $playerData);
        $this->assertSame($player->solo_victories, $playerData['solo_victories']);

        $this->assertArrayHasKey('duo_victories', $playerData);
        $this->assertSame($player->duo_victories, $playerData['duo_victories']);

        $this->assertArrayHasKey('trio_victories', $playerData);
        $this->assertSame($player->trio_victories, $playerData['trio_victories']);

        $this->assertArrayHasKey('best_time_robo_rumble', $playerData);
        $this->assertSame($player->best_time_robo_rumble, $playerData['best_time_robo_rumble']);

        $this->assertArrayHasKey('best_time_as_big_brawler', $playerData);
        $this->assertSame($player->best_time_as_big_brawler, $playerData['best_time_as_big_brawler']);

        if ($checkRelations) {
            $player->load(self::PLAYER_RELATIONS);

            // compare related clubs

            $this->assertArrayHasKey('club', $playerData);
            $this->assertIsArray($playerData['club']);

            if ($player->club) {
                $this->assertArrayHasKey('name', $playerData['club']);
                $this->assertArrayHasKey('tag', $playerData['club']);
                $this->assertSame($player->club->name, $playerData['club']['name']);
                $this->assertSame($player->club->tag, $playerData['club']['tag']);
            } else {
                $this->assertEmpty($playerData['club']);
            }

            // compare related brawlers

            $this->assertIsArray($playerData['brawlers']);
            $this->assertCount($player->brawlers->count(), $playerData['brawlers']);

            foreach ($player->brawlers as $i => $brawler) {
                $this->assertInstanceOf(Brawler::class, $brawler);

                $this->assertArrayHasKey($i, $playerData['brawlers']);
                $brawlerData = $playerData['brawlers'][$i];
                $this->assertIsArray($brawlerData);

                /** @var PlayerBrawler $playerBrawler */
                $playerBrawler = $brawler->player_brawler;
                $this->assertInstanceOf(PlayerBrawler::class, $playerBrawler);

                // todo
//                $this->assertPlayerBrawlerDTOMatchesEloquentModel($playerBrawlerDTO, $playerBrawler);
            }
        }
    }

    public function assertPlayerEloquentModelMatchesPlayerDTO(
        PlayerDTO $playerDTO,
        Player $player,
        bool $checkRelations = true,
    ) : void
    {
        $player->refresh();

        $this->assertSame($player->tag, $playerDTO->tag);
        $this->assertSame($player->name, $playerDTO->name);
        $this->assertSame($player->name_color, $playerDTO->nameColor);
        $this->assertSame($player->icon_id, $playerDTO->icon['id']);
        $this->assertSame($player->trophies, $playerDTO->trophies);
        $this->assertSame($player->highest_trophies, $playerDTO->highestTrophies);
        $this->assertSame($player->exp_level, $playerDTO->expLevel);
        $this->assertSame($player->exp_points, $playerDTO->expPoints);
        $this->assertSame($player->is_qualified_from_championship_league, $playerDTO->isQualifiedFromChampionshipChallenge);
        $this->assertSame($player->solo_victories, $playerDTO->victoriesSolo);
        $this->assertSame($player->duo_victories, $playerDTO->victoriesDuo);
        $this->assertSame($player->trio_victories, $playerDTO->victories3vs3);
        $this->assertSame($player->best_time_robo_rumble, $playerDTO->bestRoboRumbleTime);
        $this->assertSame($player->best_time_as_big_brawler, $playerDTO->bestTimeAsBigBrawler);

        if ($checkRelations) {
            $player->load(self::PLAYER_RELATIONS);

            // compare related clubs

            $this->assertIsArray($playerDTO->club);

            if ($player->club) {
                $this->assertArrayHasKey('name', $playerDTO->club);
                $this->assertArrayHasKey('tag', $playerDTO->club);
                $this->assertSame($player->club->name, $playerDTO->club['name']);
                $this->assertSame($player->club->tag, $playerDTO->club['tag']);
            } else {
                $this->assertEmpty($playerDTO->club);
            }

            // compare related brawlers

            $this->assertIsArray($playerDTO->playerBrawlers);
            $this->assertCount($player->brawlers->count(), $playerDTO->playerBrawlers);

            foreach ($playerDTO->playerBrawlers as $playerBrawlerDTO) {
                $this->assertInstanceOf(PlayerBrawlerDTO::class, $playerBrawlerDTO);

                /** @var Brawler $brawler */
                $brawler = $player->brawlers->first(
                    fn(Brawler $br) => (($br->ext_id === $playerBrawlerDTO->extId) && ($br->name === $playerBrawlerDTO->name))
                );
                $this->assertInstanceOf(Brawler::class, $brawler);

                /** @var PlayerBrawler $playerBrawler */
                $playerBrawler = $brawler->player_brawler;
                $this->assertInstanceOf(PlayerBrawler::class, $playerBrawler);

                $this->assertPlayerBrawlerDTOMatchesEloquentModel($playerBrawlerDTO, $playerBrawler);
            }
        }
    }

    /**
     * Covers consistency between DTO and data array.
     *
     * @param PlayerDTO $playerDTO
     * @param array $playerData
     * @return void
     */
    public function assertPlayerDTOMatchesDataArray(PlayerDTO $playerDTO, array $playerData): void
    {
        $this->assertSame($playerData['tag'], $playerDTO->tag);
        $this->assertSame($playerData['name'], $playerDTO->name);
        $this->assertSame($playerData['nameColor'], $playerDTO->nameColor);
        $this->assertSame($playerData['icon']['id'], $playerDTO->icon['id']);
        $this->assertSame($playerData['trophies'], $playerDTO->trophies);
        $this->assertSame($playerData['highestTrophies'], $playerDTO->highestTrophies);
        $this->assertSame($playerData['expLevel'], $playerDTO->expLevel);
        $this->assertSame($playerData['expPoints'], $playerDTO->expPoints);
        $this->assertSame($playerData['isQualifiedFromChampionshipChallenge'], $playerDTO->isQualifiedFromChampionshipChallenge);
        $this->assertSame($playerData['soloVictories'], $playerDTO->victoriesSolo);
        $this->assertSame($playerData['duoVictories'], $playerDTO->victoriesDuo);
        $this->assertSame($playerData['3vs3Victories'], $playerDTO->victories3vs3);
        $this->assertSame($playerData['bestRoboRumbleTime'], $playerDTO->bestRoboRumbleTime);
        $this->assertSame($playerData['bestTimeAsBigBrawler'], $playerDTO->bestTimeAsBigBrawler);

        $this->assertArrayHasKey('club', $playerData);
        $clubData = $playerData['club'];
        $this->assertIsArray($clubData);
        $this->assertIsArray($playerDTO->club);

        if (empty($playerDTO->club)) {
            $this->assertEmpty($clubData);
        } else {
            $this->assertArrayHasKey('tag', $playerDTO->club);
            $this->assertArrayHasKey('name', $playerDTO->club);
            $this->assertArrayHasKey('tag', $clubData);
            $this->assertArrayHasKey('name', $clubData);
            $this->assertSame($clubData['tag'], $playerDTO->club['tag']);
            $this->assertSame($clubData['name'], $playerDTO->club['name']);
        }

        $this->assertArrayHasKey('brawlers', $playerData);
        $brawlersData = $playerData['brawlers'];
        $this->assertIsArray($brawlersData);
        $this->assertIsArray($playerDTO->playerBrawlers);
        $this->assertCount(sizeof($brawlersData), $playerDTO->playerBrawlers);

        foreach ($brawlersData as $i => $playerBrawlerData) {
            $playerBrawlerDTO = $playerDTO->playerBrawlers[$i];
            $this->assertInstanceOf(PlayerBrawlerDTO::class, $playerBrawlerDTO);
            $this->assertIsArray($playerBrawlerData);
            $this->assertPlayerBrawlerDTOMatchesDataArray($playerBrawlerDTO, $playerBrawlerData);
        }
    }

    public function assertPlayerBrawlerDTOMatchesDataArray(PlayerBrawlerDTO $dto, array $array): void
    {
        return;
        // todo
        dd(
            $dto,
            $array,
        );
    }

    public function assertPlayerBrawlerDTOMatchesEloquentModel(PlayerBrawlerDTO $dto, PlayerBrawler $playerBrawler): void
    {
        $this->assertSame($dto->power, $playerBrawler->power);
        $this->assertSame($dto->rank, $playerBrawler->rank);
        $this->assertSame($dto->trophies, $playerBrawler->trophies);
        $this->assertSame($dto->highestTrophies, $playerBrawler->highest_trophies);

        $playerBrawler->load(self::PLAYER_BRAWLER_RELATIONS);

        $this->assertInstanceOf(Brawler::class, $playerBrawler->brawler);
        $this->assertSame($dto->extId, $playerBrawler->brawler->ext_id);
        $this->assertSame($dto->name, $playerBrawler->brawler->name);

        foreach ($dto->accessories as $playerBrawlerAccessoryDTO) {
            $this->assertInstanceOf(PlayerBrawlerAccessoryDTO::class, $playerBrawlerAccessoryDTO);

            /** @var PlayerBrawlerAccessory $playerBrawlerAccessory */
            $playerBrawlerAccessory = $playerBrawler->playerBrawlerAccessories->first(
                fn(PlayerBrawlerAccessory $pba) => (
                    ($pba->accessory->ext_id === $playerBrawlerAccessoryDTO->extId) &&
                    ($pba->accessory->name === $playerBrawlerAccessoryDTO->name)
                )
            );

            $this->assertInstanceOf(PlayerBrawlerAccessory::class, $playerBrawlerAccessory);
            $this->assertSame($playerBrawlerAccessoryDTO->extId, $playerBrawlerAccessory->accessory->ext_id);
            $this->assertSame($playerBrawlerAccessoryDTO->name, $playerBrawlerAccessory->accessory->name);
        }

        foreach ($dto->gears as $playerBrawlerGearDTO) {
            $this->assertInstanceOf(PlayerBrawlerGearDTO::class, $playerBrawlerGearDTO);

            $playerBrawlerGear = $playerBrawler->playerBrawlerGears->first(
                fn(PlayerBrawlerGear $a) => (
                    ($a->gear->ext_id === $playerBrawlerGearDTO->extId) &&
                    ($a->gear->name === $playerBrawlerGearDTO->name) &&
                    ($a->level === $playerBrawlerGearDTO->level)
                )
            );
            $this->assertInstanceOf(PlayerBrawlerGear::class, $playerBrawlerGear);
            $this->assertSame($playerBrawlerGearDTO->level, $playerBrawlerGear->level);
            $this->assertSame($playerBrawlerGearDTO->extId, $playerBrawlerGear->gear->ext_id);
            $this->assertSame($playerBrawlerGearDTO->name, $playerBrawlerGear->gear->name);
        }

        foreach ($dto->starPowers as $playerBrawlerStarPowerDTO) {
            $this->assertInstanceOf(PlayerBrawlerStarPowerDTO::class, $playerBrawlerStarPowerDTO);

            $playerBrawlerStarPower = $playerBrawler->playerBrawlerStarPowers->first(
                fn(PlayerBrawlerStarPower $a) => (
                    ($a->starPower->ext_id === $playerBrawlerStarPowerDTO->extId) &&
                    ($a->starPower->name === $playerBrawlerStarPowerDTO->name)
                )
            );
            $this->assertInstanceOf(PlayerBrawlerStarPower::class, $playerBrawlerStarPower);
            $this->assertSame($playerBrawlerStarPowerDTO->extId, $playerBrawlerStarPower->starPower->ext_id);
            $this->assertSame($playerBrawlerStarPowerDTO->name, $playerBrawlerStarPower->starPower->name);
        }
    }

    public static function providePlayerDTOData(): array
    {
        return [
            'player' => [
                [
                    'tag' => '#12345',
                    'name' => 'Test Player 1',
                    'nameColor' => '#fff000',
                    'icon' => ['id' => 123],
                    'trophies' => 50000,
                    'highestTrophies' => 50123,
                    'expLevel' => 45,
                    'expPoints' => 1000,
                    'isQualifiedFromChampionshipChallenge' => true,
                    'soloVictories' => 3000,
                    'duoVictories' => 2500,
                    '3vs3Victories' => 3300,
                    'bestRoboRumbleTime' => 99,
                    'bestTimeAsBigBrawler' => 60,
                    'club' => [],
                    'brawlers' => [],
                ],
            ],
            'player with club' => [
                [
                    'tag' => '#12345',
                    'name' => 'Test Player 2',
                    'nameColor' => '#fff000',
                    'icon' => ['id' => 123],
                    'trophies' => 50000,
                    'highestTrophies' => 50123,
                    'expLevel' => 45,
                    'expPoints' => 1000,
                    'isQualifiedFromChampionshipChallenge' => true,
                    'soloVictories' => 3000,
                    'duoVictories' => 2500,
                    '3vs3Victories' => 3300,
                    'bestRoboRumbleTime' => 99,
                    'bestTimeAsBigBrawler' => 60,
                    'club' => [
                        'tag' => '#777',
                        'name' => 'Test Club with 1 member',
                    ],
                    'brawlers' => [],
                ],
            ],
            'player with club and 1 brawler' => [
                [
                    'tag' => '#PLA123',
                    'name' => 'Test Player 3',
                    'nameColor' => '#fff000',
                    'icon' => ['id' => 123],
                    'trophies' => 50000,
                    'highestTrophies' => 50123,
                    'expLevel' => 45,
                    'expPoints' => 1000,
                    'isQualifiedFromChampionshipChallenge' => true,
                    'soloVictories' => 3000,
                    'duoVictories' => 2500,
                    '3vs3Victories' => 3300,
                    'bestRoboRumbleTime' => 99,
                    'bestTimeAsBigBrawler' => 60,
                    'club' => [
                        'tag' => '#CLU777',
                        'name' => 'Test Club with 1 member',
                    ],
                    'brawlers' => [
                        [
                            'id' => 16000000,
                            'name' => 'Test Brawler',
                            'power' => 11,
                            'rank' => 50,
                            'trophies' => 1000,
                            'highestTrophies' => 1050,
                            'gadgets' => [
                                [
                                    'id' => 23000255,
                                    'name' => "FAST FORWARD",
                                ],
                                [
                                    'id' => 23000288,
                                    'name' => "CLAY PIGEONS",
                                ],
                            ],
                            'gears' => [
                                [
                                    'id' => 62000002,
                                    'name' => "DAMAGE",
                                    'level' => 3,
                                ],
                                [
                                    'id' => 62000017,
                                    'name' => "GADGET COOLDOWN",
                                    'level' => 4,
                                ],
                                [
                                    'id' => 62000004,
                                    'name' => "SHIELD",
                                    'level' => 5,
                                ],
                            ],
                            'starPowers' => [
                                [
                                    'id' => 23000076,
                                    'name' => "SHELL SHOCK",
                                ],
                                [
                                    'id' => 23000135,
                                    'name' => "BAND-AID",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function providePlayerModelData(): array
    {
        return [
            'player' => [
                [
                    'tag' => '#12345',
                    'name' => 'Test Player 1',
                    'name_color' => '#fff000',
                    'icon_id' => 123,
                    'trophies' => 50000,
                    'highest_trophies' => 50123,
                    'exp_level' => 45,
                    'exp_points' => 1000,
                    'is_qualified_from_championship_league' => true,
                    'solo_victories' => 3000,
                    'duo_victories' => 2500,
                    'trio_victories' => 3300,
                    'best_time_robo_rumble' => 99,
                    'best_time_as_big_brawler' => 60,
                    'club' => [],
                    'brawlers' => [],
                ],
            ],
            'player with club' => [
                [
                    'tag' => '#12345',
                    'name' => 'Test Player 2',
                    'name_color' => '#fff000',
                    'icon_id' => 123,
                    'trophies' => 50000,
                    'highest_trophies' => 50123,
                    'exp_level' => 45,
                    'exp_points' => 1000,
                    'is_qualified_from_championship_league' => true,
                    'solo_victories' => 3000,
                    'duo_victories' => 2500,
                    'trio_victories' => 3300,
                    'best_time_robo_rumble' => 99,
                    'best_time_as_big_brawler' => 60,
                    'club' => [
                        'tag' => '#777',
                        'name' => 'Test Club with 1 member',
                    ],
                    'brawlers' => [],
                ],
//            ],
//            'player with club and 1 brawler' => [
//                [
//                    'tag' => '#12345',
//                    'name' => 'Test Player 3',
//                    'name_color' => '#fff000',
//                    'icon_id' => 123,
//                    'trophies' => 50000,
//                    'highest_trophies' => 50123,
//                    'exp_level' => 45,
//                    'exp_points' => 1000,
//                    'is_qualified_from_championship_league' => true,
//                    'solo_victories' => 3000,
//                    'duo_victories' => 2500,
//                    'trio_victories' => 3300,
//                    'best_time_robo_rumble' => 99,
//                    'best_time_as_big_brawler' => 60,
//                    'club' => [
//                        'tag' => '#777',
//                        'name' => 'Test Club with 1 member',
//                    ],
//                    'brawlers' => [],
//                ],
            ],
        ];
    }
}
