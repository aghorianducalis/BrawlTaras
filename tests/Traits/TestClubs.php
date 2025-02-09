<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Club;
use App\Models\Player;
use Database\Factories\ClubFactory;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Club::class)]
#[UsesClass(ClubDTO::class)]
#[UsesClass(ClubFactory::class)]
trait TestClubs
{
    use TestPlayers;

    /**
     * Create a club with associated players.
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     * @param int $memberCount
     * @return Club
     */
    public function createClubWithMembers(
        array|callable $attributes = [],
        int            $memberCount = 10,
    ) : Club {
        return Club::factory()
            ->withMembers($memberCount)
            ->create($attributes);
    }

    /**
     * Covers consistency between DTO and Eloquent model.
     *
     * @param ClubDTO $clubDTO
     * @param Club $club
     * @return void
     */
    public function assertClubDTOMatchesEloquentModel(ClubDTO $clubDTO, Club $club): void
    {
        $this->assertEquals($club->tag, $clubDTO->tag);
        $this->assertEquals($club->name, $clubDTO->name);
        $this->assertEquals($club->description, $clubDTO->description);
        $this->assertEquals($club->type, $clubDTO->type);
        $this->assertEquals($club->badge_id, $clubDTO->badgeId);
        $this->assertEquals($club->required_trophies, $clubDTO->requiredTrophies);
        $this->assertEquals($club->trophies, $clubDTO->trophies);

        $club->load(['members']);

        if ($club->members->isEmpty()) {
            $this->assertEmpty($clubDTO->members);
        } else {
            $this->assertIsArray($clubDTO->members);
            $this->assertCount($club->members->count(), $clubDTO->members);

            foreach ($club->members as $i => $player) {
                $playerDTO = $clubDTO->members[$i];

                $this->assertInstanceOf(Player::class, $player);
                $this->assertInstanceOf(PlayerDTO::class, $playerDTO);
                $this->assertPlayerDTOMatchesEloquentModel($playerDTO, $player);
            }
        }
    }

    /**
     * Covers consistency between DTO and data array.
     *
     * @param ClubDTO $clubDTO
     * @param array $clubData
     * @return void
     */
    public function assertClubDTOMatchesDataArray(ClubDTO $clubDTO, array $clubData): void
    {
        $this->assertEquals($clubData['tag'], $clubDTO->tag);
        $this->assertEquals($clubData['name'] ?? null, $clubDTO->name);
        $this->assertEquals($clubData['description'] ?? null, $clubDTO->description);
        $this->assertEquals($clubData['type'] ?? null, $clubDTO->type);
        $this->assertEquals($clubData['badgeId'] ?? null, $clubDTO->badgeId);
        $this->assertEquals($clubData['requiredTrophies'] ?? null, $clubDTO->requiredTrophies);
        $this->assertEquals($clubData['trophies'] ?? null, $clubDTO->trophies);

        if (empty($clubData['members'])) {
            $this->assertEmpty($clubDTO->members);
        } else {
            $this->assertIsArray($clubDTO->members);
            $this->assertCount(sizeof($clubData['members']), $clubDTO->members);

            foreach ($clubData['members'] as $i => $playerData) {
                $playerDTO = $clubDTO->members[$i];

                $this->assertInstanceOf(PlayerDTO::class, $playerDTO);
                $this->assertPlayerDTOMatchesDataArray($playerDTO, $playerData);
            }
        }
    }

    public function assertEqualClubModels(Club $clubExpected, Club $clubActual): void
    {
        $this->assertSame($clubExpected->id, $clubActual->id);
        $this->assertSame($clubExpected->tag, $clubActual->tag);
        $this->assertSame($clubExpected->name, $clubActual->name);
        $this->assertSame($clubExpected->description, $clubActual->description);
        $this->assertSame($clubExpected->type, $clubActual->type);
        $this->assertSame($clubExpected->badge_id, $clubActual->badge_id);
        $this->assertSame($clubExpected->required_trophies, $clubActual->required_trophies);
        $this->assertSame($clubExpected->trophies, $clubActual->trophies);
        $this->assertTrue($clubExpected->created_at->equalTo($clubActual->created_at));

        // compare the club members
        $clubExpected->load(['members']);
        $clubActual->load(['members']);

        $this->assertEquals(
            $clubExpected->members->toArray(),
            $clubActual->members->toArray()
        );
    }

    public static function provideClubData(): array
    {
        return [
            'club with tag only' => [
                [
                    'tag' => '#123ABC',
                ],
            ],
            'club without members' => [
                [
                    'tag' => '#12345',
                    'name' => 'Test Club without members',
                    'description' => 'A club without members for testing.',
                    'type' => Club::CLUB_TYPES[1],
                    'badgeId' => 1001,
                    'requiredTrophies' => 500,
                    'trophies' => 2000,
                    'members' => [],
                ],
            ],
            'club with 1 member' => [
                [
                    'tag' => '#777',
                    'name' => 'Test Club with 1 member',
                    'description' => 'A club for testing.',
                    'type' => Club::CLUB_TYPES[0],
                    'badgeId' => 2025,
                    'requiredTrophies' => 30000,
                    'trophies' => 150000,
                    'members' => [
                        [
                            'tag' => '#ABC123',
                            'name' => 'Test Player',
                            'nameColor' => '#FFFFFF',
                            'role' => Club::CLUB_MEMBER_ROLES[0],
                            'trophies' => 1000,
                            'icon' => ['id' => 1],
                        ],
                    ],
                ],
            ],
        ];
    }
}
