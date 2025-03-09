<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\PlayerBrawlerDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Brawler;
use App\Models\Player;
use App\Models\PlayerBrawler;
use Database\Factories\PlayerFactory;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Player::class)]
#[UsesClass(PlayerBrawler::class)]
#[UsesClass(PlayerFactory::class)]
#[UsesClass(PlayerDTO::class)]
#[UsesClass(PlayerBrawlerDTO::class)]
trait TestPlayers
{
    public function createPlayer(
        array|callable $attributes = [],
    ) : Player {
        return Player::factory()
            ->withClub()
            ->withBrawlers()
            ->create($attributes);
    }

    public function assertEqualPlayerModels(Player $playerExpected, Player $playerActual): void
    {
        $playerExpected->refresh();
        $playerActual->refresh();
        $playerExpected->load(['club', 'brawlers']);
        $playerActual->load(['club', 'brawlers']);

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

        $this->assertEquals(
            $playerExpected->club?->toArray(),
            $playerActual->club?->toArray()
        );

        // todo method
        $this->assertEquals(
            $playerExpected->brawlers->toArray(),
            $playerActual->brawlers->toArray()
        );
    }

    public function assertPlayerDTOMatchesEloquentModel(PlayerDTO $playerDTO, Player $player, bool $checkRelations = true): void
    {
        $player->refresh();
        $player->load(['club', 'brawlers']);

        $this->assertEquals($playerDTO->tag, $player->tag);
        $this->assertEquals($playerDTO->name, $player->name);
        $this->assertEquals($playerDTO->nameColor, $player->name_color);
        $this->assertEquals($playerDTO->icon['id'], $player->icon_id);
        $this->assertEquals($playerDTO->trophies, $player->trophies);
        $this->assertEquals($playerDTO->highestTrophies, $player->highest_trophies);
        $this->assertEquals($playerDTO->expLevel, $player->exp_level);
        $this->assertEquals($playerDTO->expPoints, $player->exp_points);
        $this->assertEquals($playerDTO->isQualifiedFromChampionshipChallenge, $player->is_qualified_from_championship_league);
        $this->assertEquals($playerDTO->victoriesSolo, $player->solo_victories);
        $this->assertEquals($playerDTO->victoriesDuo, $player->duo_victories);
        $this->assertEquals($playerDTO->victories3vs3, $player->trio_victories);
        $this->assertEquals($playerDTO->bestRoboRumbleTime, $player->best_time_robo_rumble);
        $this->assertEquals($playerDTO->bestTimeAsBigBrawler, $player->best_time_as_big_brawler);

        if ($checkRelations) {

            // compare related clubs

            $this->assertIsArray($playerDTO->club);

            if ($player->club) {
                $this->assertArrayHasKey('tag', $playerDTO->club);
                $this->assertArrayHasKey('name', $playerDTO->club);
                $this->assertEquals($playerDTO->club['name'], $player->club->name);
                $this->assertEquals($playerDTO->club['tag'], $player->club->tag);
            } else {
                $this->assertEmpty($playerDTO->club);
            }

            // compare related brawlers

            $this->assertIsArray($playerDTO->playerBrawlers);

            if ($player->brawlers->isNotEmpty()) {
                $this->assertCount($player->brawlers->count(), $playerDTO->playerBrawlers);

                foreach ($playerDTO->playerBrawlers as $i => $playerBrawlerDTO) {
                    $this->assertInstanceOf(PlayerBrawlerDTO::class, $playerBrawlerDTO);

                    /** @var Brawler $brawler */
                    $brawler = $player->brawlers->get($i);
                    $this->assertInstanceOf(Brawler::class, $brawler);

                    /** @var PlayerBrawler $playerBrawler */
                    $playerBrawler = $brawler->player_brawler;
                    $this->assertInstanceOf(PlayerBrawler::class, $playerBrawler);

                    $this->assertPlayerBrawlerDTOMatchesEloquentModel($playerBrawlerDTO, $playerBrawler);
                }
            } else {
                $this->assertEmpty($playerDTO->playerBrawlers);
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
        $this->assertEquals($playerData['tag'], $playerDTO->tag);
        $this->assertEquals($playerData['name'], $playerDTO->name);
        $this->assertEquals($playerData['nameColor'], $playerDTO->nameColor);
        $this->assertEquals($playerData['icon']['id'], $playerDTO->icon['id']);
        $this->assertEquals($playerData['trophies'], $playerDTO->trophies);
        $this->assertEquals($playerData['highestTrophies'], $playerDTO->highestTrophies);
        $this->assertEquals($playerData['expLevel'], $playerDTO->expLevel);
        $this->assertEquals($playerData['expPoints'], $playerDTO->expPoints);
        $this->assertEquals($playerData['isQualifiedFromChampionshipChallenge'], $playerDTO->isQualifiedFromChampionshipChallenge);
        $this->assertEquals($playerData['soloVictories'], $playerDTO->victoriesSolo);
        $this->assertEquals($playerData['duoVictories'], $playerDTO->victoriesDuo);
        $this->assertEquals($playerData['3vs3Victories'], $playerDTO->victories3vs3);
        $this->assertEquals($playerData['bestRoboRumbleTime'], $playerDTO->bestRoboRumbleTime);
        $this->assertEquals($playerData['bestTimeAsBigBrawler'], $playerDTO->bestTimeAsBigBrawler);

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
            $this->assertEquals($clubData['tag'], $playerDTO->club['tag']);
            $this->assertEquals($clubData['name'], $playerDTO->club['name']);
        }

        $this->assertArrayHasKey('brawlers', $playerData);
        $brawlersData = $playerData['brawlers'];
        $this->assertIsArray($brawlersData);
        $this->assertIsArray($playerDTO->playerBrawlers);

        if (empty($playerDTO->playerBrawlers)) {
            $this->assertEmpty($brawlersData);
        } else {
            $this->assertCount(sizeof($playerDTO->playerBrawlers), $brawlersData);

            foreach ($brawlersData as $i => $playerBrawlerData) {
                $playerBrawlerDTO = $playerDTO->playerBrawlers[$i];
                $this->assertInstanceOf(PlayerBrawlerDTO::class, $playerBrawlerDTO);

                $this->assertIsArray($playerBrawlerData);
                $this->assertPlayerBrawlerDTOMatchesDataArray($playerBrawlerDTO, $playerBrawlerData);
            }
        }
    }

    public function assertPlayerBrawlerDTOMatchesDataArray(PlayerBrawlerDTO $dto, array $array): void
    {
        // todo
    }

    public function assertPlayerBrawlerDTOMatchesEloquentModel(PlayerBrawlerDTO $dto, PlayerBrawler $model): void
    {
        // todo
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
//            ],
//            'player with club and 1 brawler' => [
//                [
//                    'tag' => '#12345',
//                    'name' => 'Test Player 3',
//                    'nameColor' => '#fff000',
//                    'icon' => ['id' => 123],
//                    'trophies' => 50000,
//                    'highestTrophies' => 50123,
//                    'expLevel' => 45,
//                    'expPoints' => 1000,
//                    'isQualifiedFromChampionshipChallenge' => true,
//                    'soloVictories' => 3000,
//                    'duoVictories' => 2500,
//                    '3vs3Victories' => 3300,
//                    'bestRoboRumbleTime' => 99,
//                    'bestTimeAsBigBrawler' => 60,
//                    'club' => [
//                        'tag' => '#777',
//                        'name' => 'Test Club with 1 member',
//                    ],
//                    'brawlers' => [],
//                ],
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
                    'icon' => ['id' => 123],
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
                    'icon' => ['id' => 123],
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
//                    'icon' => ['id' => 123],
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
