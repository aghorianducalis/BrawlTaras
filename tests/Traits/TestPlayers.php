<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\BrawlerDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Player;

trait TestPlayers
{
    public function assertEqualPlayerModels(Player $playerExpected, Player $playerActual): void
    {
        $this->assertSame($playerExpected->id, $playerActual->id);
        $this->assertSame($playerExpected->tag, $playerActual->tag);
        $this->assertSame($playerExpected->name, $playerActual->name);
        $this->assertSame($playerExpected->club_id, $playerActual->club_id);
        $this->assertTrue($playerExpected->created_at->equalTo($playerActual->created_at));

        // compare the player's relations
        $playerExpected->load(['club']);
        $playerActual->load(['club']);

        $this->assertEquals(
            $playerExpected->club?->toArray(),
            $playerActual->club?->toArray()
        );
    }

    public function assertPlayerDTOMatchesEloquentModel(PlayerDTO $playerDTO, Player $player): void
    {
        $this->assertEquals($player->tag, $playerDTO->tag);
        $this->assertEquals($player->name, $playerDTO->name);
        $this->assertEquals($player->name_color, $playerDTO->nameColor);
        $this->assertEquals($player->club_role, $playerDTO->clubRole);
        $this->assertEquals($player->trophies, $playerDTO->trophies);
        $this->assertEquals($player->icon_id, $playerDTO->icon['id']);
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

        if (isset($playerData['highestTrophies'])) {
            $this->assertEquals($playerData['highestTrophies'], $playerDTO->highestTrophies);
        }

        if (isset($playerData['expLevel'])) {
            $this->assertEquals($playerData['expLevel'], $playerDTO->expLevel);
        }

        if (isset($playerData['expPoints'])) {
            $this->assertEquals($playerData['expPoints'], $playerDTO->expPoints);
        }

        if (isset($playerData['isQualifiedFromChampionshipChallenge'])) {
            $this->assertEquals($playerData['isQualifiedFromChampionshipChallenge'], $playerDTO->isQualifiedFromChampionshipChallenge);
        }

        if (isset($playerData['soloVictories'])) {
            $this->assertEquals($playerData['soloVictories'], $playerDTO->victoriesSolo);
        }

        if (isset($playerData['duoVictories'])) {
            $this->assertEquals($playerData['duoVictories'], $playerDTO->victoriesDuo);
        }

        if (isset($playerData['3vs3Victories'])) {
            $this->assertEquals($playerData['3vs3Victories'], $playerDTO->victories3vs3);
        }

        if (isset($playerData['bestRoboRumbleTime'])) {
            $this->assertEquals($playerData['bestRoboRumbleTime'], $playerDTO->bestRoboRumbleTime);
        }

        if (isset($playerData['bestTimeAsBigBrawler'])) {
            $this->assertEquals($playerData['bestTimeAsBigBrawler'], $playerDTO->bestTimeAsBigBrawler);
        }

        if (isset($playerData['role'])) {
            $this->assertEquals($playerData['role'], $playerDTO->clubRole);
        }

        if (key_exists('club', $playerData)) {
            $clubData = $playerData['club'];

            if (is_null($clubData)) {
                $this->assertEquals($clubData, $playerDTO->club);
            } else {
                $this->assertIsArray($clubData);
                $this->assertArrayHasKey('tag', $clubData);
                $this->assertArrayHasKey('name', $clubData);
                $this->assertIsArray($playerDTO->club);
                $this->assertArrayHasKey('tag', $playerDTO->club);
                $this->assertArrayHasKey('name', $playerDTO->club);

                $this->assertEquals($clubData['tag'], $playerDTO->club['tag']);
                $this->assertEquals($clubData['name'], $playerDTO->club['name']);
            }
        }

        if (isset($playerData['brawlers']) && is_array($playerData['brawlers'])) {
            foreach ($playerData['brawlers'] as $i => $brawlerData) {
                $this->assertIsArray($brawlerData);
                $this->assertInstanceOf(BrawlerDTO::class, $playerDTO->brawlers[$i]);
                $this->assertBrawlerDTOMatchesDataArray($playerDTO->brawlers[$i], $brawlerData);
            }
        }
    }

    public function assertBrawlerDTOMatchesDataArray(BrawlerDTO $brawlerDTO, array $brawlerData): void
    {}

    public function createPlayer(
        array|callable    $attributes = [],
    ) : Player {
        return Player::factory()
//            ->withBrawlers()
            ->withClub()
            ->create($attributes);
    }

    public static function providePlayerData(): array
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
//                    'role' => null,
//                    'club' => null,
//                    'brawlers' => null,
                ],
            ],
//            'player with club' => [
//                [
//                    'tag' => '#12345',
//                    'name' => 'Test Player 2',
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
//                    'role' => Club::CLUB_MEMBER_ROLES[1],
//                    'club' => [
//                        'tag' => '#777',
//                        'name' => 'Test Club with 1 member',
//                    ],
//                ],
//            ],
//            'player without brawlers' => [
//            ],
//            'player with 1 brawler' => [
//            ],
        ];
    }
}
