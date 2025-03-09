<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\ClubMemberDTO;
use App\Models\Club;
use App\Models\Player;
use Database\Factories\ClubFactory;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Club::class)]
#[UsesClass(ClubFactory::class)]
#[UsesClass(ClubDTO::class)]
#[UsesClass(ClubMemberDTO::class)]
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

        $this->assertIsArray($clubDTO->members);

        $club->load(['members']);

        if ($club->members->isEmpty()) {
            $this->assertEmpty($clubDTO->members);
        } else {
            $this->assertCount($club->members->count(), $clubDTO->members);

            foreach ($clubDTO->members as $i => $memberDTO) {
                $this->assertInstanceOf(ClubMemberDTO::class, $memberDTO);
                $member = $club->members->get($i);
                $this->assertInstanceOf(Player::class, $member);
                $this->assertClubMemberDTOMatchesEloquentModel($memberDTO, $member);
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
        $this->assertArrayHasKey('tag', $clubData);
        $this->assertEquals($clubDTO->tag, $clubData['tag']);

        $this->assertArrayHasKey('name', $clubData);
        $this->assertEquals($clubDTO->name, $clubData['name']);

        $this->assertArrayHasKey('description', $clubData);
        $this->assertEquals($clubDTO->description, $clubData['description']);

        $this->assertArrayHasKey('type', $clubData);
        $this->assertEquals($clubDTO->type, $clubData['type']);

        $this->assertArrayHasKey('badgeId', $clubData);
        $this->assertEquals($clubDTO->badgeId, $clubData['badgeId']);

        $this->assertArrayHasKey('requiredTrophies', $clubData);
        $this->assertEquals($clubDTO->requiredTrophies, $clubData['requiredTrophies']);

        $this->assertArrayHasKey('trophies', $clubData);
        $this->assertEquals($clubDTO->trophies, $clubData['trophies']);

        $this->assertIsArray($clubData['members']);

        if (empty($clubDTO->members)) {
            $this->assertEmpty($clubData['members']);
        } else {
            $this->assertIsArray($clubDTO->members);
            $this->assertCount(sizeof($clubDTO->members), $clubData['members']);

            foreach ($clubDTO->members as $i => $memberDTO) {
                $this->assertInstanceOf(ClubMemberDTO::class, $memberDTO);

                $this->assertTrue(isset($clubData['members'][$i]));
                $memberData = $clubData['members'][$i];
                $this->assertIsArray($memberData);

                $this->assertClubMemberDTOMatchesDataArray($memberDTO, $memberData);
            }
        }
    }

    /**
     * Covers consistency between Eloquent model and data array.
     *
     * @param Club $club
     * @param array $clubData
     * @param bool $checkMembers
     * @return void
     */
    public function assertClubEloquentModelMatchesDataArray(Club $club, array $clubData, bool $checkMembers = true): void
    {
        $club->refresh();
        $club->load(['members']);

        $this->assertArrayHasKey('tag', $clubData);
        $this->assertEquals($club->tag, $clubData['tag']);

        $this->assertArrayHasKey('name', $clubData);
        $this->assertEquals($club->name, $clubData['name']);

        $this->assertArrayHasKey('description', $clubData);
        $this->assertEquals($club->description, $clubData['description']);

        $this->assertArrayHasKey('type', $clubData);
        $this->assertEquals($club->type, $clubData['type']);

        $this->assertArrayHasKey('badge_id', $clubData);
        $this->assertEquals($club->badge_id, $clubData['badge_id']);

        $this->assertArrayHasKey('required_trophies', $clubData);
        $this->assertEquals($club->required_trophies, $clubData['required_trophies']);

        $this->assertArrayHasKey('trophies', $clubData);
        $this->assertEquals($club->trophies, $clubData['trophies']);

        if ($checkMembers) {
            $this->assertIsArray($clubData['members']);

            if ($club->members->isEmpty()) {
                $this->assertEmpty($clubData['members']);
            } else {
                $this->assertIsArray($club->members);
                $this->assertCount(sizeof($club->members), $clubData['members']);

                foreach ($club->members as $i => $player) {
                    $this->assertInstanceOf(Player::class, $player);

                    $this->assertTrue(isset($clubData['members'][$i]));
                    $memberData = $clubData['members'][$i];
                    $this->assertIsArray($memberData);

                    $this->assertClubMemberEloquentModelMatchesDataArray($player, $memberData);
                }
            }
        }
    }

    public function assertClubMemberDTOMatchesEloquentModel(ClubMemberDTO $memberDTO, Player $member): void
    {
        $this->assertEquals($memberDTO->tag, $member->tag);
        $this->assertEquals($memberDTO->name, $member->name);
        $this->assertEquals($memberDTO->nameColor, $member->name_color);
        $this->assertEquals($memberDTO->role, $member->club_role);
        $this->assertEquals($memberDTO->trophies, $member->trophies);
        $this->assertEquals($memberDTO->icon['id'], $member->icon_id);
    }

    public function assertClubMemberDTOMatchesDataArray(ClubMemberDTO $memberDTO, array $memberData): void
    {
        $this->assertArrayHasKey('tag', $memberData);
        $this->assertEquals($memberDTO->tag, $memberData['tag']);

        $this->assertArrayHasKey('name', $memberData);
        $this->assertEquals($memberDTO->name, $memberData['name']);

        $this->assertArrayHasKey('nameColor', $memberData);
        $this->assertEquals($memberDTO->nameColor, $memberData['nameColor']);

        $this->assertArrayHasKey('role', $memberData);
        $this->assertEquals($memberDTO->role, $memberData['role']);

        $this->assertArrayHasKey('trophies', $memberData);
        $this->assertEquals($memberDTO->trophies, $memberData['trophies']);

        $this->assertArrayHasKey('icon', $memberData);
        $this->assertIsArray($memberData['icon']);
        $this->assertArrayHasKey('id', $memberData['icon']);
        $this->assertEquals($memberDTO->icon['id'], $memberData['icon']['id']);
    }

    /**
     * Covers consistency between Eloquent model and data array.
     *
     * @param Player $player
     * @param array $memberData
     * @return void
     */
    public function assertClubMemberEloquentModelMatchesDataArray(Player $player, array $memberData): void
    {
        $player->refresh();

        $this->assertArrayHasKey('tag', $memberData);
        $this->assertEquals($player->tag, $memberData['tag']);

        $this->assertArrayHasKey('name', $memberData);
        $this->assertEquals($player->name, $memberData['name']);

        $this->assertArrayHasKey('name_color', $memberData);
        $this->assertEquals($player->name_color, $memberData['name_color']);

        $this->assertArrayHasKey('role', $memberData);
        $this->assertEquals($player->club_role, $memberData['role']);

        $this->assertArrayHasKey('trophies', $memberData);
        $this->assertEquals($player->trophies, $memberData['trophies']);

        $this->assertArrayHasKey('icon', $memberData);
        $this->assertIsArray($memberData['icon']);
        $this->assertArrayHasKey('id', $memberData['icon']);
        $this->assertEquals($player->icon_id, $memberData['icon']['id']);
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

        $clubExpected->load(['members']);
        $clubActual->load(['members']);

        $this->assertEquals(
            $clubExpected->members->toArray(),
            $clubActual->members->toArray()
        );
    }

    public static function provideClubDTOData(): array
    {
        return [
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
                    'description' => 'A club with 1 member for testing.',
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

    public static function provideClubModelData(): array
    {
        return [
            'club without members' => [
                [
                    'tag' => '#12345',
                    'name' => 'Test Club without members',
                    'description' => 'A club without members for testing.',
                    'type' => Club::CLUB_TYPES[1],
                    'badge_id' => 1001,
                    'required_trophies' => 500,
                    'trophies' => 2000,
                    'members' => [],
                ],
            ],
            'club with 1 member' => [
                [
                    'tag' => '#777',
                    'name' => 'Test Club with 1 member',
                    'description' => 'A club with 1 member for testing.',
                    'type' => Club::CLUB_TYPES[0],
                    'badge_id' => 2025,
                    'required_trophies' => 30000,
                    'trophies' => 150000,
                    'members' => [
                        [
                            'tag' => '#ABC123',
                            'name' => 'Test Player',
                            'name_color' => '#FFFFFF',
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
