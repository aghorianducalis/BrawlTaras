<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\API\DTO\Response\ClubDTO;
use App\API\DTO\Response\PlayerDTO;
use App\Models\Club;
use Database\Factories\ClubFactory;
use PHPUnit\Framework\Attributes\UsesClass;

#[UsesClass(Club::class)]
#[UsesClass(ClubDTO::class)]
#[UsesClass(ClubFactory::class)]
trait TestClubs
{
    use CreatesPlayers;

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
     * @param Club $club
     * @param ClubDTO $clubDTO
     * @return void
     */
    public function assertClubDTOMatchesEloquentModel(Club $club, ClubDTO $clubDTO): void
    {
        $this->assertEquals($club->tag, $clubDTO->tag);
        $this->assertEquals($club->name, $clubDTO->name);
        $this->assertEquals($club->description, $clubDTO->description);
        $this->assertEquals($club->type, $clubDTO->type);
        $this->assertEquals($club->badge_id, $clubDTO->badgeId);
        $this->assertEquals($club->required_trophies, $clubDTO->requiredTrophies);
        $this->assertEquals($club->trophies, $clubDTO->trophies);

        if ($club->members->isEmpty()) {
            $this->assertEmpty($clubDTO->members);
        } else {
            $this->assertIsArray($clubDTO->members);
            $this->assertCount($club->members->count(), $clubDTO->members);

            foreach ($club->members as $i => $player) {
                $playerDTO = $clubDTO->members[$i];

                $this->assertInstanceOf(PlayerDTO::class, $playerDTO);
                $this->assertPlayerModelMatchesDTO($player, $playerDTO);
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
        $this->assertEquals($clubData['name'], $clubDTO->name);
        $this->assertEquals($clubData['description'], $clubDTO->description);
        $this->assertEquals($clubData['type'], $clubDTO->type);
        $this->assertEquals($clubData['badgeId'], $clubDTO->badgeId);
        $this->assertEquals($clubData['requiredTrophies'], $clubDTO->requiredTrophies);
        $this->assertEquals($clubData['trophies'], $clubDTO->trophies);

        if (empty($clubData['members'])) {
            $this->assertEmpty($clubDTO->members);
        } else {
            $this->assertIsArray($clubDTO->members);
            $this->assertCount(sizeof($clubData['members']), $clubDTO->members);

            foreach ($clubData['members'] as $i => $playerData) {
                $playerDTO = $clubDTO->members[$i];

                $this->assertInstanceOf(PlayerDTO::class, $playerDTO);
                // todo move to trait
                $this->assertEquals($playerData['tag'], $playerDTO->tag);
                $this->assertEquals($playerData['name'], $playerDTO->name);
                $this->assertEquals($playerData['nameColor'], $playerDTO->nameColor);
                $this->assertEquals($playerData['role'], $playerDTO->clubRole);
                $this->assertEquals($playerData['trophies'], $playerDTO->trophies);
                $this->assertEquals($playerData['icon']['id'], $playerDTO->icon['id']);
            }
        }
    }
}
